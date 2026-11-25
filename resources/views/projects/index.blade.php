@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="card-box">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h5 class="m-0">{{ $title }}</h5>
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search Project Name, Area, Client, PIC...">
            </div>
            <select name="profit" class="form-select" style="width:auto" onchange="this.form.submit()">
                <option value="">Semua Profit</option>
                <option value="high" @selected(request('profit') === 'high')>≥ 50%</option>
                <option value="mid" @selected(request('profit') === 'mid')>20% – 50%</option>
                <option value="low" @selected(request('profit') === 'low')>&lt; 20%</option>
            </select>
            <a href="{{ route('projects.create') }}" class="btn btn-primary-x"><i class="bi bi-plus-lg"></i> Add Project</a>
        </form>
    </div>

    <div class="table-responsive table-wrap">
        <table class="table table-bordered mb-0">
            <thead>
                <tr>
                    <th>No</th><th>Client Name</th><th>Project Name</th><th>Project Number</th><th>Project Area</th>
                    <th>PIC</th><th>Description</th><th>Status</th><th>Profit</th><th>SPK Dibayar</th><th>Created Date</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($rows as $p)
                @php
                    $profit = $p->profit_percent;
                    $profitClass = $profit === null ? 'pill-grey' : ($profit >= 50 ? 'pill-green' : ($profit >= 10 ? 'pill-orange' : 'pill-red'));
                    $statusClass = ['open' => 'pill-green', 'done' => 'pill-blue', 'canceled' => 'pill-red'][$p->status];
                @endphp
                <tr>
                    <td class="mid">{{ $rows->firstItem() + $loop->index }}</td>
                    <td class="mid">{{ $p->client->nama }}</td>
                    <td class="mid"><a href="{{ route('projects.edit', $p) }}">{{ $p->nama }}</a></td>
                    <td class="mid text-nowrap">{{ $p->number }}</td>
                    <td class="mid">{{ $p->area }}</td>
                    <td class="mid">{{ $p->pic->nama }}</td>
                    <td class="mid">{{ $p->description }}</td>
                    <td class="mid"><span class="pill {{ $statusClass }}">{{ \App\Models\Project::STATUS[$p->status] }}</span></td>
                    <td class="mid"><span class="pill {{ $profitClass }}">{{ $profit === null ? '-' : number_format($profit, 2, '.', '') . '%' }}</span></td>
                    <td class="mid text-nowrap">{{ \App\Support\Fmt::rp($p->spk_paid) }}</td>
                    <td class="mid text-nowrap">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                    <td class="mid text-nowrap">
                        <a href="{{ route('projects.edit', $p) }}" class="btn btn-sm btn-warning-x"><i class="bi bi-pencil"></i> Edit</a>
                        <form method="POST" action="{{ route('projects.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Hapus project ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger-x"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="12" class="text-center text-muted py-4">Belum ada project.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">{{ $rows->links() }}</div>
</div>
@endsection
