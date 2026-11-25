<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

abstract class MasterController extends Controller
{
    /**
     * model   : class Eloquent
     * route   : prefix nama route (mis. 'clients')
     * title   : judul singkat (mis. 'Client')
     * columns : kolom tabel  key => label
     * fields  : isi form     key => [label, required(bool), col(6|12)]
     * search  : kolom yang dicari lewat kotak search
     */
    abstract protected function cfg(): array;

    public function index(Request $r)
    {
        $c = $this->cfg();
        $model = $c['model'];
        $q = $model::query();

        if ($s = trim((string) $r->q)) {
            $q->where(function ($w) use ($c, $s) {
                foreach ($c['search'] as $col) {
                    $w->orWhere($col, 'like', "%{$s}%");
                }
            });
        }

        $rows = $q->latest('id')->paginate(10)->withQueryString();

        return view('master.index', compact('c', 'rows'));
    }

    public function create()
    {
        return view('master.form', ['c' => $this->cfg(), 'row' => null]);
    }

    public function store(Request $r)
    {
        $c = $this->cfg();
        $model = $c['model'];
        $model::create($r->validate($this->rules($c)));

        return redirect()->route($c['route'] . '.index')->with('ok', $c['title'] . ' berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $c = $this->cfg();
        $model = $c['model'];

        return view('master.form', ['c' => $c, 'row' => $model::findOrFail($id)]);
    }

    public function update(Request $r, $id)
    {
        $c = $this->cfg();
        $model = $c['model'];
        $model::findOrFail($id)->update($r->validate($this->rules($c)));

        return redirect()->route($c['route'] . '.index')->with('ok', $c['title'] . ' berhasil diubah.');
    }

    public function destroy($id)
    {
        $c = $this->cfg();
        $model = $c['model'];

        try {
            $model::findOrFail($id)->delete();
        } catch (QueryException $e) {
            return back()->with('err', $c['title'] . ' masih dipakai di data lain, tidak bisa dihapus.');
        }

        return back()->with('ok', $c['title'] . ' dihapus.');
    }

    /** Tombol "Download" -> CSV (bisa dibuka di Excel) */
    public function export()
    {
        $c = $this->cfg();
        $model = $c['model'];
        $keys = array_keys($c['columns']);

        return response()->streamDownload(function () use ($model, $c, $keys) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8
            fputcsv($out, array_values($c['columns']));
            foreach ($model::orderBy('id')->cursor() as $row) {
                fputcsv($out, array_map(fn ($k) => (string) $row->{$k}, $keys));
            }
            fclose($out);
        }, $c['route'] . '-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    protected function rules(array $c): array
    {
        $rules = [];
        foreach ($c['fields'] as $key => $f) {
            $rules[$key] = ($f[1] ? 'required' : 'nullable') . '|string|max:255';
        }

        return $rules;
    }
}
