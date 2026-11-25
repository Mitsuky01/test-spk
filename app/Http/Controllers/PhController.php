<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Ph;
use App\Models\Pic;
use App\Models\Project;
use App\Support\DocTotals;
use App\Support\Numbering;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PhController extends Controller
{
    public function index(Request $r)
    {
        $q = Ph::with(['project', 'client', 'pic']);

        if ($s = trim((string) $r->q)) {
            $q->where(function ($w) use ($s) {
                $w->where('number', 'like', "%{$s}%")
                    ->orWhere('po_number', 'like', "%{$s}%")
                    ->orWhereHas('project', fn ($c) => $c->where('nama', 'like', "%{$s}%"))
                    ->orWhereHas('client', fn ($c) => $c->where('nama', 'like', "%{$s}%"))
                    ->orWhereHas('pic', fn ($c) => $c->where('nama', 'like', "%{$s}%"));
            });
        }
        if (array_key_exists((string) $r->status, Ph::STATUS)) {
            $q->where('status', $r->status);
        }

        $rows = $q->latest('id')->paginate(10)->withQueryString();

        // Kartu ringkasan (semua data, tidak terpengaruh pencarian)
        $all = Ph::all();
        $live = $all->where('status', '!=', 'canceled');
        $summary = [
            'net' => $live->sum(fn ($p) => $p->net),
            'ppn' => $live->sum('tax_amount'),
            'cards' => collect(Ph::STATUS)->map(fn ($label, $k) => [
                'label' => $label,
                'net' => $all->where('status', $k)->sum(fn ($p) => $p->net),
                'ppn' => $all->where('status', $k)->sum('tax_amount'),
            ])->values(),
        ];

        return view('phs.index', compact('rows', 'summary'));
    }

    public function create(Request $r)
    {
        return view('phs.form', $this->formData(null) + ['prefillProject' => $r->project_id]);
    }

    public function store(Request $r)
    {
        $data = $this->validated($r);

        $ph = DB::transaction(function () use ($r, $data) {
            $calc = DocTotals::calc($data['items'], $data['discount'] ?? 0, $data['tax_type']);

            $ph = Ph::create($this->header($data, $calc) + [
                'number' => Numbering::ph(Carbon::parse($data['tanggal'])),
                'status' => 'on_process',
            ]);
            $this->saveItems($ph, $calc['rows']);
            $this->saveFile($ph, $r);

            return $ph;
        });

        return redirect()->route('phs.index')->with('ok', "PH {$ph->number} berhasil disimpan.");
    }

    public function edit(Ph $ph)
    {
        return view('phs.form', $this->formData($ph->load('items')) + ['prefillProject' => null]);
    }

    public function update(Request $r, Ph $ph)
    {
        $data = $this->validated($r);

        DB::transaction(function () use ($r, $data, $ph) {
            $calc = DocTotals::calc($data['items'], $data['discount'] ?? 0, $data['tax_type']);

            $ph->update($this->header($data, $calc));
            $ph->items()->delete();
            $this->saveItems($ph, $calc['rows']);
            $this->saveFile($ph, $r);
        });

        return redirect()->route('phs.index')->with('ok', "PH {$ph->number} berhasil diubah.");
    }

    public function updateStatus(Request $r, Ph $ph)
    {
        $ph->update($r->validate(['status' => ['required', Rule::in(array_keys(Ph::STATUS))]]));

        return back()->with('ok', "Status {$ph->number} diubah.");
    }

    public function destroy(Ph $ph)
    {
        if ($ph->file_path) {
            Storage::disk('public')->delete($ph->file_path);
        }
        $ph->delete();

        return back()->with('ok', 'PH dihapus.');
    }

    public function export()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['No PH', 'Tanggal', 'Project', 'Client', 'PIC', 'Subtotal', 'Discount', 'Tax', 'PPN', 'Grand Total', 'PO Number', 'Status']);
            foreach (Ph::with(['project', 'client', 'pic'])->orderBy('id')->cursor() as $p) {
                fputcsv($out, [$p->number, $p->tanggal->format('d/m/Y'), $p->project->nama, $p->client->nama, $p->pic->nama,
                    $p->subtotal, $p->discount, $p->tax_type, $p->tax_amount, $p->grand_total, $p->po_number, Ph::STATUS[$p->status] ?? $p->status]);
            }
            fclose($out);
        }, 'ph-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    // ------------------------------------------------------------------

    private function formData(?Ph $ph): array
    {
        $projects = Project::orderByDesc('id')->get();

        return [
            'ph' => $ph,
            'number' => $ph?->number ?? Numbering::ph(),
            'projects' => $projects,
            'projectMap' => $projects->mapWithKeys(fn ($p) => [$p->id => [
                'client_id' => $p->client_id, 'pic_id' => $p->pic_id, 'area' => $p->area,
            ]]),
            'clients' => Client::orderBy('nama')->get(),
            'pics' => Pic::orderBy('nama')->get(),
        ];
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'tanggal' => 'required|date',
            'project_id' => 'required|exists:projects,id',
            'client_id' => 'required|exists:clients,id',
            'pic_id' => 'required|exists:pics,id',
            'area' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'po_number' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'tax_type' => ['required', Rule::in(['NON PPN', 'PPN'])],
            'note' => 'nullable|string',
            'file' => 'nullable|file|mimes:xlsx,xls,csv,pdf,doc,docx|max:10240',
            'items' => 'required|array|min:1',
            'items.*.item' => 'required|string|max:255',
            'items.*.description' => 'nullable|string|max:255',
            'items.*.product_detail' => 'nullable|string|max:255',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);
    }

    private function header(array $d, array $calc): array
    {
        return Arr::only($d, ['tanggal', 'project_id', 'client_id', 'pic_id', 'area', 'description', 'po_number', 'tax_type', 'note'])
            + Arr::only($calc, ['subtotal', 'discount', 'tax_amount', 'grand_total']);
    }

    private function saveItems(Ph $ph, array $rows): void
    {
        foreach ($rows as $row) {
            $ph->items()->create(Arr::only($row, ['item', 'description', 'product_detail', 'qty', 'price', 'total']));
        }
    }

    private function saveFile(Ph $ph, Request $r): void
    {
        if (! $r->hasFile('file')) {
            return;
        }
        if ($ph->file_path) {
            Storage::disk('public')->delete($ph->file_path);
        }
        $ph->update([
            'file_path' => $r->file('file')->store('ph', 'public'),
            'file_name' => $r->file('file')->getClientOriginalName(),
        ]);
    }
}
