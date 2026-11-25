@extends('layouts.app')
@section('title', 'PH')
@section('content')
@php
    $badge = ['on_process' => 'pill-soft', 'released' => 'pill-orange', 'po' => 'pill-blue', 'invoice' => 'pill-purple', 'canceled' => 'pill-red'];
@endphp
<div class="card-box">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h5 class="m-0">PH Management</h5>
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <div class="search-box">
                <i class="bi bi-funnel"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search PH Number, Project, PIC, Client, PO...">
            </div>
            <select name="status" class="form-select" style="width:auto" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                @foreach (\App\Models\Ph::STATUS as $k => $label)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>
                @endforeach
            </select>
            <a href="{{ route('phs.create') }}" class="btn btn-primary-x"><i class="bi bi-plus-lg"></i> Add PH</a>
        </form>
    </div>

    <div class="sum-wrap">
        <div class="sum-main mb-3">
            <div class="big">Total PH (Excl PPN) {{ \App\Support\Fmt::rp($summary['net']) }}</div>
            <small>PPN 11% - {{ \App\Support\Fmt::rp($summary['ppn']) }}</small>
            <small>Tidak termasuk PH yang dibatalkan</small>
        </div>
        <div class="row g-3">
            @foreach ($summary['cards'] as $card)
                <div class="col-md-4">
                    <div class="sum-box">
                        <div class="big">{{ $card['label'] }} (Excl. PPN) {{ \App\Support\Fmt::rp($card['net']) }}</div>
                        <small>{{ $card['ppn'] > 0 ? 'PPN 11% - ' . \App\Support\Fmt::rp($card['ppn']) : 'Tidak ada data pajak' }}</small>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <a href="{{ route('phs.export') }}" class="d-inline-block mb-2"><i class="bi bi-download"></i> Export Excel</a>

    <div class="table-responsive table-wrap">
        <table class="table table-bordered mb-0">
            <thead><tr><th>No</th><th style="min-width:300px">PH Info</th><th style="min-width:220px">Financial Details</th><th>SPK</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse ($rows as $ph)
                <tr>
                    <td>{{ $rows->firstItem() + $loop->index }}</td>
                    <td>
                        <div><b>Project :</b> <a href="{{ route('phs.edit', $ph) }}">{{ $ph->project->nama }}</a></div>
                        <div><b>PH Number :</b> <a href="{{ route('phs.edit', $ph) }}">{{ $ph->number }}</a></div>
                        <div><b>PH Date :</b> {{ $ph->tanggal->translatedFormat('j F Y') }}</div>
                        <div><b>Client :</b> {{ $ph->client->nama }}</div>
                        <div><b>PIC :</b> {{ $ph->pic->nama }}</div>
                        <div><b>PO Number :</b> {{ $ph->po_number }}</div>
                        <div><b>Created Date :</b> {{ $ph->created_at->format('d/m/Y H:i') }}</div>
                        <div><b>File PH :</b>
                            @if ($ph->file_path)
                                <a href="{{ asset('storage/' . $ph->file_path) }}" target="_blank"><i class="bi bi-file-earmark"></i> {{ \Illuminate\Support\Str::limit($ph->file_name, 28) }}</a>
                            @else - @endif
                        </div>
                    </td>
                    <td>
                        <div><b>Subtotal :</b> {{ \App\Support\Fmt::rp($ph->subtotal) }}</div>
                        <div><b>Discount :</b> {{ \App\Support\Fmt::rp($ph->discount) }}</div>
                        <div><b>{{ $ph->tax_type === 'PPN' ? 'PPN ' . config('spk.ppn_rate') . '%' : 'NON PPN' }} :</b> {{ \App\Support\Fmt::rp($ph->tax_amount) }}</div>
                        <div><b>Grand Total :</b> {{ \App\Support\Fmt::rp($ph->grand_total) }}</div>
                        <div><b>WHT :</b> Rp 0</div>
                        <div><b>Rest of Cost :</b> {{ \App\Support\Fmt::rp($ph->rest) }}</div>
                    </td>
                    <td class="mid">
                        <a href="{{ route('spks.create', ['project_id' => $ph->project_id]) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-plus"></i> SPK</a>
                    </td>
                    <td class="mid">
                        <form method="POST" action="{{ route('phs.status', $ph) }}">
                            @csrf @method('PATCH')
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                @foreach (\App\Models\Ph::STATUS as $k => $label)
                                    <option value="{{ $k }}" @selected($ph->status === $k)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                        <span class="pill {{ $badge[$ph->status] }} mt-2">{{ \App\Models\Ph::STATUS[$ph->status] }}</span>
                    </td>
                    <td class="mid text-nowrap">
                        <a href="{{ route('phs.edit', $ph) }}" class="btn btn-sm btn-warning-x"><i class="bi bi-pencil"></i> Edit</a>
                        <form method="POST" action="{{ route('phs.destroy', $ph) }}" class="d-inline" onsubmit="return confirm('Hapus PH ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger-x"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada PH.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">{{ $rows->links() }}</div>
</div>
@endsection
