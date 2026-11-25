<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Pic;
use App\Models\Project;
use App\Models\Spk;
use App\Models\SpkPayment;
use App\Models\Vendor;
use App\Support\DocTotals;
use App\Support\Numbering;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;

class SpkController extends Controller
{
    public function index(Request $r)
    {
        $q = Spk::with(['vendor', 'project', 'pic', 'payments', 'items']);

        if ($s = trim((string) $r->q)) {
            $q->where(function ($w) use ($s) {
                $w->where('number', 'like', "%{$s}%")
                    ->orWhere('tax_type', 'like', "%{$s}%")
                    ->orWhere('status', 'like', "%{$s}%")
                    ->orWhereHas('project', fn ($c) => $c->where('nama', 'like', "%{$s}%"))
                    ->orWhereHas('vendor', fn ($c) => $c->where('nama', 'like', "%{$s}%"))
                    ->orWhereHas('pic', fn ($c) => $c->where('nama', 'like', "%{$s}%"));
            });
        }

        $rows = $q->latest('id')->paginate(10)->withQueryString();

        // Kartu ringkasan (semua SPK, nilai sebelum PPN)
        $all = Spk::with('payments')->get();
        $paidNet = fn ($list) => $list->sum(fn ($s) => $s->net * $s->paid_ratio);
        $summary = [
            'total' => $all->sum(fn ($s) => $s->net),
            'non_ppn' => $all->where('tax_type', 'NON PPN')->sum(fn ($s) => $s->net),
            'ppn' => $all->where('tax_type', 'PPN')->sum(fn ($s) => $s->net),
            'paid' => $paidNet($all),
            'paid_full' => $all->filter(fn ($s) => $s->paid_ratio >= 1)->sum(fn ($s) => $s->net),
            'paid_dp' => $paidNet($all->filter(fn ($s) => $s->paid_ratio > 0 && $s->paid_ratio < 1)),
            'unpaid' => $all->sum(fn ($s) => $s->net) - $paidNet($all),
            'unpaid_spk' => $all->filter(fn ($s) => $s->paid_ratio <= 0)->sum(fn ($s) => $s->net),
            'unpaid_dp' => $all->filter(fn ($s) => $s->paid_ratio > 0 && $s->paid_ratio < 1)
                ->sum(fn ($s) => $s->net * (1 - $s->paid_ratio)),
        ];

        return view('spks.index', compact('rows', 'summary'));
    }

    public function create(Request $r)
    {
        return view('spks.form', $this->formData(null) + ['prefillProject' => $r->project_id]);
    }

    public function store(Request $r)
    {
        $data = $this->validated($r);

        $spk = DB::transaction(function () use ($r, $data) {
            $calc = DocTotals::calc($data['items'], $data['discount'] ?? 0, $data['tax_type']);

            $spk = Spk::create($this->header($data, $calc) + [
                'number' => Numbering::spk(Carbon::parse($data['tanggal'])),
                'status' => 'unpaid',
            ]);
            $this->saveItems($spk, $calc['rows']);
            $this->saveFile($spk, $r);

            // Status "Paid" saat membuat SPK = langsung tercatat lunas
            if (($data['status'] ?? 'unpaid') === 'paid' && $spk->grand_total > 0) {
                $spk->payments()->create(['amount' => $spk->grand_total, 'paid_at' => $spk->tanggal, 'note' => 'Lunas saat SPK dibuat']);
                $spk->syncStatus();
            }

            return $spk;
        });

        return redirect()->route('spks.index')->with('ok', "SPK {$spk->number} berhasil disimpan.");
    }

    public function edit(Spk $spk)
    {
        return view('spks.form', $this->formData($spk->load('items', 'payments')) + ['prefillProject' => null]);
    }

    public function update(Request $r, Spk $spk)
    {
        $data = $this->validated($r);

        DB::transaction(function () use ($r, $data, $spk) {
            $calc = DocTotals::calc($data['items'], $data['discount'] ?? 0, $data['tax_type']);

            $spk->update($this->header($data, $calc));
            $spk->items()->delete();
            $this->saveItems($spk, $calc['rows']);
            $this->saveFile($spk, $r);
            $spk->syncStatus(); // total berubah -> status bisa ikut berubah
        });

        return redirect()->route('spks.index')->with('ok', "SPK {$spk->number} berhasil diubah.");
    }

    public function destroy(Spk $spk)
    {
        if ($spk->file_path) {
            Storage::disk('public')->delete($spk->file_path);
        }
        $spk->delete();

        return back()->with('ok', 'SPK dihapus.');
    }

    public function addPayment(Request $r, Spk $spk)
    {
        $d = $r->validate([
            'amount' => 'required|numeric|min:1',
            'paid_at' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        if ((int) $d['amount'] > $spk->rest) {
            return back()->with('err', 'Nominal melebihi sisa tagihan (' . \App\Support\Fmt::rp($spk->rest) . ').');
        }

        $spk->payments()->create(['amount' => (int) $d['amount'], 'paid_at' => $d['paid_at'], 'note' => $d['note'] ?? null]);
        $spk->syncStatus();

        return back()->with('ok', 'Pembayaran dicatat.');
    }

    public function deletePayment(SpkPayment $payment)
    {
        $spk = $payment->spk_id ? Spk::find($payment->spk_id) : null;
        $payment->delete();
        $spk?->syncStatus();

        return back()->with('ok', 'Pembayaran dihapus.');
    }

    public function export()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['No SPK', 'Tanggal', 'Project', 'Vendor', 'PIC', 'Subtotal', 'Discount', 'Tax', 'PPN', 'Grand Total', 'Dibayar', 'Status']);
            foreach (Spk::with(['project', 'vendor', 'pic', 'payments'])->orderBy('id')->cursor() as $s) {
                fputcsv($out, [$s->number, $s->tanggal->format('d/m/Y'), $s->project->nama, $s->vendor->nama, $s->pic->nama,
                    $s->subtotal, $s->discount, $s->tax_type, $s->tax_amount, $s->grand_total, $s->paid, Spk::STATUS[$s->status] ?? $s->status]);
            }
            fclose($out);
        }, 'spk-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    // ------------------------------------------------------------------

    private function formData(?Spk $spk): array
    {
        $projects = Project::orderByDesc('id')->get();
        $pics = Pic::orderBy('nama')->get();

        return [
            'spk' => $spk,
            'number' => $spk?->number ?? Numbering::spk(),
            'vendors' => Vendor::orderBy('nama')->get(),
            'projects' => $projects,
            'projectMap' => $projects->mapWithKeys(fn ($p) => [$p->id => [
                'client_id' => $p->client_id, 'pic_id' => $p->pic_id, 'area' => $p->area, 'nama' => $p->nama,
            ]]),
            'clients' => Client::orderBy('nama')->get(),
            'pics' => $pics,
            'picNames' => $pics->pluck('nama', 'id'),
        ];
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'tanggal' => 'required|date',
            'vendor_id' => 'required|exists:vendors,id',
            'project_id' => 'required|exists:projects,id',
            'client_id' => 'required|exists:clients,id',
            'pic_id' => 'required|exists:pics,id',
            'area' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => ['nullable', Rule::in(['unpaid', 'paid'])],
            'discount' => 'nullable|numeric|min:0',
            'tax_type' => ['required', Rule::in(['NON PPN', 'PPN'])],
            'note' => 'nullable|string',
            'file' => 'nullable|file|mimes:xlsx,xls,csv,pdf,doc,docx,jpg,jpeg,png|max:10240',
            'ttd1_name' => 'nullable|string|max:255', 'ttd1_title' => 'nullable|string|max:255',
            'ttd2_name' => 'nullable|string|max:255', 'ttd2_title' => 'nullable|string|max:255',
            'ttd3_name' => 'nullable|string|max:255', 'ttd3_title' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item' => 'required|string|max:255',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.quotation_number' => 'nullable|string|max:255',
            'items.*.project' => 'nullable|string|max:255',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);
    }

    private function header(array $d, array $calc): array
    {
        return Arr::only($d, [
            'tanggal', 'vendor_id', 'project_id', 'client_id', 'pic_id', 'area', 'description', 'tax_type', 'note',
            'ttd1_name', 'ttd1_title', 'ttd2_name', 'ttd2_title', 'ttd3_name', 'ttd3_title',
        ]) + Arr::only($calc, ['subtotal', 'discount', 'tax_amount', 'grand_total']);
    }

    private function saveItems(Spk $spk, array $rows): void
    {
        foreach ($rows as $row) {
            $spk->items()->create(Arr::only($row, ['item', 'description', 'quotation_number', 'project', 'qty', 'price', 'total']));
        }
    }

    private function saveFile(Spk $spk, Request $r): void
    {
        if (! $r->hasFile('file')) {
            return;
        }
        if ($spk->file_path) {
            Storage::disk('public')->delete($spk->file_path);
        }
        $spk->update([
            'file_path' => $r->file('file')->store('spk', 'public'),
            'file_name' => $r->file('file')->getClientOriginalName(),
        ]);
    }

    public function pdf(Spk $spk)
    {
    $spk->load(['vendor', 'project', 'client', 'pic', 'items']);

    $pdf = Pdf::loadView('spks.pdf', compact('spk'))
        ->setPaper('a4', 'portrait');

    return $pdf->download('SPK-' . str_replace(['/', '\\'], '-', $spk->number) . '.pdf');
    }
}
