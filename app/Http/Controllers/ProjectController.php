<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Pic;
use App\Models\Project;
use App\Support\Numbering;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $r)
    {
        $status = in_array($r->status, array_keys(Project::STATUS)) ? $r->status : 'open';

        $q = Project::with(['client', 'pic', 'phs', 'spks.payments'])->where('status', $status);

        if ($s = trim((string) $r->q)) {
            $q->where(function ($w) use ($s) {
                $w->where('nama', 'like', "%{$s}%")
                    ->orWhere('number', 'like', "%{$s}%")
                    ->orWhere('area', 'like', "%{$s}%")
                    ->orWhereHas('client', fn ($c) => $c->where('nama', 'like', "%{$s}%"))
                    ->orWhereHas('pic', fn ($c) => $c->where('nama', 'like', "%{$s}%"));
            });
        }

        $all = $q->latest('id')->get();

        // Profit dihitung dari PH & SPK, jadi disaring di PHP lalu dipaginasi manual.
        $all = match ($r->profit) {
            'high' => $all->filter(fn ($p) => $p->profit_percent !== null && $p->profit_percent >= 50),
            'mid' => $all->filter(fn ($p) => $p->profit_percent !== null && $p->profit_percent >= 20 && $p->profit_percent < 50),
            'low' => $all->filter(fn ($p) => $p->profit_percent !== null && $p->profit_percent < 20),
            default => $all,
        };

        $page = LengthAwarePaginator::resolveCurrentPage();
        $rows = new LengthAwarePaginator(
            $all->forPage($page, 10)->values(), $all->count(), 10, $page,
            ['path' => $r->url(), 'query' => $r->query()]
        );

        $title = ['open' => 'Project Management', 'done' => 'Project Done', 'canceled' => 'Project Canceled'][$status];

        return view('projects.index', compact('rows', 'status', 'title'));
    }

    public function create()
    {
        return view('projects.form', [
            'project' => null,
            'number' => Numbering::project(),
            'clients' => Client::orderBy('nama')->get(),
            'pics' => Pic::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $r)
    {
        $data = $this->validated($r);
        $data['number'] = Numbering::project();
        Project::create($data);

        return redirect()->route('projects.index')->with('ok', 'Project berhasil disimpan.');
    }

    public function edit(Project $project)
    {
        return view('projects.form', [
            'project' => $project,
            'number' => $project->number,
            'clients' => Client::orderBy('nama')->get(),
            'pics' => Pic::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $r, Project $project)
    {
        $data = $this->validated($r) + ['status' => $r->validate([
            'status' => ['required', Rule::in(array_keys(Project::STATUS))],
        ])['status']];
        $project->update($data);

        return redirect()->route('projects.index', ['status' => $project->status])->with('ok', 'Project berhasil diubah.');
    }

    public function destroy(Project $project)
    {
        try {
            $project->delete();
        } catch (QueryException $e) {
            return back()->with('err', 'Project sudah punya PH / SPK, tidak bisa dihapus. Ubah statusnya jadi Canceled saja.');
        }

        return back()->with('ok', 'Project dihapus.');
    }

    private function validated(Request $r): array
    {
        return $r->validate([
            'nama' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'pic_id' => 'required|exists:pics,id',
            'area' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
    }
}
