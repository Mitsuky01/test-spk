@extends('layouts.app')
@section('title', $spk ? 'Edit SPK' : 'Add SPK')
@section('content')
@php
    $blank = ['item' => '', 'description' => '', 'quotation_number' => '', 'project' => '', 'qty' => 1, 'price' => 0];
    $items = old('items', $spk
        ? $spk->items->map(fn ($i) => $i->only(['item', 'description', 'quotation_number', 'project', 'qty', 'price']))->values()->all()
        : [$blank]);
    $projectId = old('project_id', $spk->project_id ?? $prefillProject ?? '');
    $first = $projectMap[$projectId] ?? null;
    $picId = old('pic_id', $spk->pic_id ?? ($first['pic_id'] ?? ''));
    $ttd = config('spk.ttd');
    $payload = [
        'items' => array_values($items), 'blank' => $blank,
        'discount' => (int) old('discount', $spk->discount ?? 0),
        'tax' => old('tax_type', $spk->tax_type ?? 'NON PPN'),
        'ppnRate' => config('spk.ppn_rate'),
        'project' => $projectId,
        'client' => old('client_id', $spk->client_id ?? ($first['client_id'] ?? '')),
        'pic' => $picId,
        'area' => old('area', $spk->area ?? ($first['area'] ?? '')),
        'vendor' => old('vendor_id', $spk->vendor_id ?? ''),
        'ttd1' => old('ttd1_name', $spk->ttd1_name ?? ($picNames[$picId] ?? '')),
        'projects' => $projectMap,
        'picNames' => $picNames,
        'vendors' => $vendors->mapWithKeys(fn ($v) => [$v->id => $v->only(['alamat', 'phone', 'npwp', 'nama_bank', 'no_rekening', 'nama_rekening'])]),
    ];
@endphp
<div class="card-box">
    <h5 class="mb-4">{{ $spk ? 'Edit SPK' : 'Add SPK' }}</h5>

    <form method="POST" enctype="multipart/form-data" x-data="docForm({{ \Illuminate\Support\Js::from($payload) }})"
          action="{{ $spk ? route('spks.update', $spk) : route('spks.store') }}">
        @csrf
        @if ($spk) @method('PUT') @endif

        <div class="row g-4">
            {{-- Vendor --}}
            <div class="col-lg-6">
                <h6 class="fw-bold mb-3">Vendor</h6>
                <div class="mb-3">
                    <label class="lbl">Company Name <span class="req">*</span></label>
                    <select name="vendor_id" class="form-select" x-model="vendor">
                        <option value="">-- Pilih Company Name --</option>
                        @foreach ($vendors as $v)<option value="{{ $v->id }}">{{ $v->nama }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-3"><label class="lbl">Address</label><input class="form-control" readonly x-bind:value="v.alamat || ''"></div>
                <div class="mb-3"><label class="lbl">Phone</label><input class="form-control" readonly x-bind:value="v.phone || ''"></div>
                <div class="mb-3"><label class="lbl">NPWP</label><input class="form-control" readonly x-bind:value="v.npwp || ''"></div>
                <div class="row g-2">
                    <div class="col-4"><label class="lbl">Bank Name</label><input class="form-control" readonly x-bind:value="v.nama_bank || ''"></div>
                    <div class="col-4"><label class="lbl">Rec. Number</label><input class="form-control" readonly x-bind:value="v.no_rekening || ''"></div>
                    <div class="col-4"><label class="lbl">Rec. Name</label><input class="form-control" readonly x-bind:value="v.nama_rekening || ''"></div>
                </div>
            </div>

            {{-- Project detail --}}
            <div class="col-lg-6">
                <h6 class="fw-bold mb-3">Project Detail</h6>
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="lbl">Tanggal SPK <span class="req">*</span></label>
                        <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', optional($spk?->tanggal)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-5">
                        <label class="lbl">No SPK</label>
                        <input class="form-control" value="{{ $number }}" readonly>
                    </div>
                    <div class="col-md-7">
                        <label class="lbl">Project Name <span class="req">*</span></label>
                        <select name="project_id" class="form-select" x-model="project" x-on:change="pickProject()">
                            <option value="">-- Pilih Project --</option>
                            @foreach ($projects as $p)<option value="{{ $p->id }}">{{ $p->number }} - {{ $p->nama }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="lbl">Client Name <span class="req">*</span></label>
                        <select name="client_id" class="form-select" x-model="client">
                            <option value="">-- Pilih Client --</option>
                            @foreach ($clients as $c)<option value="{{ $c->id }}">{{ $c->nama }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="lbl">Area</label>
                        <input type="text" name="area" class="form-control" placeholder="Masukan Area" x-model="area">
                    </div>
                    <div class="col-12">
                        <label class="lbl">Deskripsi</label>
                        <input type="text" name="description" class="form-control" placeholder="Masukan Deskripsi" value="{{ old('description', $spk->description ?? '') }}">
                    </div>
                    <div class="col-12">
                        <label class="lbl">PIC <span class="req">*</span></label>
                        <select name="pic_id" class="form-select" x-model="pic" x-on:change="syncTtd()">
                            <option value="">-- Pilih PIC --</option>
                            @foreach ($pics as $p)<option value="{{ $p->id }}">{{ $p->nama }}</option>@endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="mt-4">
            <label class="lbl">Items <span class="req">*</span></label>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-2">
                    <thead><tr><th>Item</th><th>Description</th><th>Quotation Number</th><th>Project</th><th style="width:80px">Qty</th><th style="width:140px">Price</th><th style="width:130px">Total</th><th style="width:60px">Aksi</th></tr></thead>
                    <tbody>
                        <template x-for="(it, i) in items" x-bind:key="i">
                            <tr>
                                <td><input class="form-control form-control-sm" x-bind:name="'items['+i+'][item]'" x-model="it.item" required></td>
                                <td><input class="form-control form-control-sm" x-bind:name="'items['+i+'][description]'" x-model="it.description"></td>
                                <td><input class="form-control form-control-sm" x-bind:name="'items['+i+'][quotation_number]'" x-model="it.quotation_number"></td>
                                <td><input class="form-control form-control-sm" x-bind:name="'items['+i+'][project]'" x-model="it.project"></td>
                                <td><input type="number" min="1" class="form-control form-control-sm" x-bind:name="'items['+i+'][qty]'" x-model.number="it.qty"></td>
                                <td><input type="number" min="0" class="form-control form-control-sm" x-bind:name="'items['+i+'][price]'" x-model.number="it.price"></td>
                                <td class="text-end" x-text="rp(rowTotal(it))"></td>
                                <td class="text-center"><button type="button" class="btn btn-sm btn-danger-x" x-on:click="remove(i)"><i class="bi bi-x-lg"></i></button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-primary-x btn-sm" x-on:click="add()">+ Add Item</button>
        </div>

        <div class="row g-4 mt-1">
            <div class="col-lg-6">
                <label class="lbl">Status</label>
                @if ($spk)
                    <div><span class="pill {{ ['paid' => 'pill-green', 'dp' => 'pill-orange', 'unpaid' => 'pill-red'][$spk->status] }}">{{ \App\Models\Spk::STATUS[$spk->status] }}</span>
                        <span class="small text-muted ms-2">Ubah lewat tombol Payment di daftar SPK.</span></div>
                @else
                    <select name="status" class="form-select">
                        <option value="unpaid" @selected(old('status') === 'unpaid')>Unpaid</option>
                        <option value="paid" @selected(old('status') === 'paid')>Paid (langsung dicatat lunas)</option>
                    </select>
                @endif

                <label class="lbl mt-4">Upload File</label>
                <input type="file" name="file" class="form-control">
                @if ($spk && $spk->file_name)<div class="small text-muted mt-1">File saat ini: {{ $spk->file_name }} (upload baru untuk mengganti)</div>@endif

                <label class="lbl mt-4">Note</label>
                <textarea name="note" rows="7" class="form-control">{{ old('note', $spk->note ?? config('spk.spk_note')) }}</textarea>
            </div>
            <div class="col-lg-6">
                <div class="calc-box">
                    <div class="head">Calculation</div>
                    <div class="body">
                        <label class="lbl">Discount</label>
                        <input type="number" min="0" name="discount" class="form-control mb-3" placeholder="Masukan Discount" x-model.number="discount">
                        <label class="lbl">Subtotal</label>
                        <input type="text" class="form-control mb-3 text-end" x-bind:value="rp(subtotal)" readonly>
                        <label class="lbl">Tax <span class="req">*</span></label>
                        <select name="tax_type" class="form-select mb-3" x-model="tax">
                            <option value="NON PPN">NON PPN</option>
                            <option value="PPN">PPN {{ config('spk.ppn_rate') }}%</option>
                        </select>
                        <template x-if="tax === 'PPN'">
                            <div class="d-flex justify-content-between mb-3 small"><span>PPN {{ config('spk.ppn_rate') }}%</span><b x-text="'Rp ' + rp(taxAmount)"></b></div>
                        </template>
                        <label class="lbl">Grand Total</label>
                        <input type="text" class="form-control text-end fw-bold" x-bind:value="rp(grand)" readonly>
                    </div>
                </div>
            </div>
        </div>

        {{-- Penandatanganan --}}
        <h6 class="fw-bold mt-5 mb-3">Penandatangan</h6>
        <div class="row g-3">
            <div class="col-lg-6"><label class="lbl">Nama TTD 1 (PIC)</label><input name="ttd1_name" class="form-control" x-model="ttd1"></div>
            <div class="col-lg-6"><label class="lbl">Jabatan TTD 1</label><input name="ttd1_title" class="form-control" value="{{ old('ttd1_title', $spk->ttd1_title ?? $ttd[1]['title'] ?? '') }}"></div>
            <div class="col-lg-6"><label class="lbl">Nama TTD 2</label><input name="ttd2_name" class="form-control" value="{{ old('ttd2_name', $spk->ttd2_name ?? $ttd[2]['name'] ?? '') }}"></div>
            <div class="col-lg-6"><label class="lbl">Jabatan TTD 2</label><input name="ttd2_title" class="form-control" value="{{ old('ttd2_title', $spk->ttd2_title ?? $ttd[2]['title'] ?? '') }}"></div>
            <div class="col-lg-6"><label class="lbl">Nama TTD 3</label><input name="ttd3_name" class="form-control" value="{{ old('ttd3_name', $spk->ttd3_name ?? $ttd[3]['name'] ?? '') }}"></div>
            <div class="col-lg-6"><label class="lbl">Jabatan TTD 3</label><input name="ttd3_title" class="form-control" value="{{ old('ttd3_title', $spk->ttd3_title ?? $ttd[3]['title'] ?? '') }}"></div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('spks.index') }}" class="btn btn-outline-secondary btn-sm px-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <button class="btn btn-primary-x btn-sm px-3" type="submit"><i class="bi bi-save"></i> Save SPK</button>
        </div>
    </form>
</div>
@endsection
