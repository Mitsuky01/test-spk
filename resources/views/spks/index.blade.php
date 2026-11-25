@extends('layouts.app')
@section('title', 'SPK')
@section('content')
@php
    $statusClass = ['paid' => 'pill-green', 'dp' => 'pill-orange', 'unpaid' => 'pill-red'];
    $rp = fn ($n) => \App\Support\Fmt::rp($n);
@endphp
<div class="card-box">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h5 class="m-0">SPK Management</h5>
        <form method="GET" class="d-flex gap-2 flex-wrap">
            <div class="search-box">
                <i class="bi bi-funnel"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search SPK Number, Project, PIC, Vendor, PPN, Status...">
            </div>
            <a href="{{ route('spks.create') }}" class="btn btn-primary-x"><i class="bi bi-plus-lg"></i> Add SPK</a>
        </form>
    </div>

    <div class="sum-wrap">
        <div class="sum-main mb-3">
            <div class="big">Total SPK (Excl PPN) {{ $rp($summary['total']) }}</div>
            <small>NON PPN - {{ $rp($summary['non_ppn']) }}</small>
            <small>PPN {{ config('spk.ppn_rate') }}% - {{ $rp($summary['ppn']) }}</small>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="sum-box">
                    <div class="big">SPK Paid (Excl. PPN) {{ $rp($summary['paid']) }}</div>
                    <small>Full Paid - {{ $rp($summary['paid_full']) }}</small>
                    <small>DP Paid - {{ $rp($summary['paid_dp']) }}</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="sum-box">
                    <div class="big">SPK Unpaid (Excl. PPN) {{ $rp($summary['unpaid']) }}</div>
                    <small>Rincian SPK Belum Dibayar - {{ $rp($summary['unpaid_spk']) }}</small>
                    <small>Rincian Sisa DP Belum Dibayar - {{ $rp($summary['unpaid_dp']) }}</small>
                </div>
            </div>
        </div>
    </div>

    <a href="{{ route('spks.export') }}" class="d-inline-block mb-2"><i class="bi bi-download"></i> Export Excel</a>

    <div class="table-responsive table-wrap">
        <table class="table table-bordered mb-0">
            <thead><tr><th>No</th><th style="min-width:300px">SPK Info</th><th style="min-width:200px">Financial Details</th><th style="min-width:170px">Payment</th><th>Status</th><th>Created Date</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse ($rows as $spk)
                @php $pct = round($spk->paid_ratio * 100); @endphp
                <tr>
                    <td><a href="{{ route('spks.edit', $spk) }}" title="Edit"><i class="bi bi-pencil"></i></a> {{ $rows->firstItem() + $loop->index }}</td>
                    <td>
                        <div><b>Project :</b> <a href="{{ route('projects.edit', $spk->project_id) }}">{{ $spk->project->nama }}</a></div>
                        <div><b>SPK Number :</b> <a href="{{ route('spks.edit', $spk) }}">{{ $spk->number }}</a></div>
                        <div><b>SPK Date :</b> {{ $spk->tanggal->translatedFormat('j F Y') }}</div>
                        <div><b>Vendor :</b> {{ $spk->vendor->nama }}</div>
                        <div><b>PIC :</b> {{ $spk->pic->nama }}</div>
                        <div><b>Quotation :</b> {{ $spk->items->pluck('quotation_number')->filter()->unique()->implode(', ') }}</div>
                    </td>
                    <td>
                        <div><b>Total Cost :</b> {{ $rp($spk->subtotal) }}</div>
                        <div><b>{{ $spk->tax_type === 'PPN' ? 'PPN ' . config('spk.ppn_rate') . '%' : 'NON PPN' }} :</b> {{ $rp($spk->tax_amount) }}</div>
                        <div><b>Grand Total :</b> {{ $rp($spk->grand_total) }}</div>
                        <div><b>WHT :</b> Rp 0</div>
                        <div><b>Rest of Cost :</b> {{ $rp($spk->rest) }}</div>
                    </td>
                    <td>
                        <div><b>Total Payment : {{ $rp($spk->paid) }}</b></div>
                        <div class="progress my-1"><div class="progress-bar bg-success" style="width: {{ $pct }}%"></div></div>
                        <div class="small">{{ $pct }}%</div>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#pay{{ $spk->id }}" class="small"><i class="bi bi-cash-coin"></i> Payment</a>
                    </td>
                    <td class="mid"><span class="pill {{ $statusClass[$spk->status] }}">{{ \App\Models\Spk::STATUS[$spk->status] }}</span></td>
                    <td class="mid text-nowrap">{{ $spk->created_at->format('d/m/Y H:i') }}</td>
                    <td class="mid text-nowrap">
                        @if ($spk->file_path)
                            <a href="{{ asset('storage/' . $spk->file_path) }}" target="_blank" class="btn btn-sm btn-primary-x">
                                <i class="bi bi-file-earmark"></i> File
                            </a>
                        @endif

                    {{-- PDF --}}
                    <a href="{{ route('spks.pdf', $spk) }}" target="_blank" class="btn btn-sm btn-danger-x" title="Download PDF"><i class="bi bi-file-earmark-pdf"></i></a>

                    {{-- Edit --}}
                    <a href="{{ route('spks.edit', $spk) }}" class="btn btn-sm btn-warning-x"><i class="bi bi-pencil"></i></a>

                    {{-- Delete --}}
                            <form method="POST" action="{{ route('spks.destroy', $spk) }}" class="d-inline" onsubmit="return confirm('Hapus SPK ini?')">
                             @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger-x"><i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                    {{-- <td class="mid text-nowrap">
                        @if ($spk->file_path)
                            <a href="{{ asset('storage/' . $spk->file_path) }}" target="_blank" class="btn btn-sm btn-primary-x"><i class="bi bi-file-earmark"></i> File</a>
                        @endif
                        <a href="{{ route('spks.edit', $spk) }}" class="btn btn-sm btn-warning-x"><i class="bi bi-pencil"></i></a>
                        <form method="POST" action="{{ route('spks.destroy', $spk) }}" class="d-inline" onsubmit="return confirm('Hapus SPK ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger-x"><i class="bi bi-trash"></i></button>
                        </form>
                    </td> --}}
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada SPK.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">{{ $rows->links() }}</div>
</div>

{{-- Modal pembayaran per SPK --}}
@foreach ($rows as $spk)
<div class="modal fade" id="pay{{ $spk->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Pembayaran {{ $spk->number }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between small mb-2">
                    <span>Grand Total: <b>{{ $rp($spk->grand_total) }}</b></span>
                    <span>Sisa: <b>{{ $rp($spk->rest) }}</b></span>
                </div>

                @if ($spk->payments->isNotEmpty())
                    <table class="table table-sm table-bordered">
                        <thead><tr><th>Tanggal</th><th class="text-end">Nominal</th><th>Catatan</th><th></th></tr></thead>
                        <tbody>
                        @foreach ($spk->payments as $pay)
                            <tr>
                                <td>{{ $pay->paid_at->format('d/m/Y') }}</td>
                                <td class="text-end">{{ $rp($pay->amount) }}</td>
                                <td>{{ $pay->note }}</td>
                                <td>
                                    <form method="POST" action="{{ route('spks.payments.destroy', $pay) }}" onsubmit="return confirm('Hapus pembayaran ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger-x py-0"><i class="bi bi-x"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif

                @if ($spk->rest > 0)
                    <form method="POST" action="{{ route('spks.payments.store', $spk) }}" class="row g-2">
                        @csrf
                        <div class="col-6">
                            <label class="lbl small">Nominal</label>
                            <input type="number" name="amount" min="1" max="{{ $spk->rest }}" value="{{ $spk->rest }}" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6">
                            <label class="lbl small">Tanggal</label>
                            <input type="date" name="paid_at" value="{{ now()->format('Y-m-d') }}" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12">
                            <label class="lbl small">Catatan (DP, pelunasan, dll)</label>
                            <input type="text" name="note" class="form-control form-control-sm">
                        </div>
                        <div class="col-12"><button class="btn btn-primary-x btn-sm">Catat Pembayaran</button></div>
                    </form>
                @else
                    <div class="alert alert-success py-2 mb-0">Sudah lunas.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
