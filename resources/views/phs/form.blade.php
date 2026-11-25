@extends('layouts.app')
@section('title', $ph ? 'Edit PH' : 'Add PH')
@section('content')
@php
    $blank = ['item' => '', 'description' => '', 'product_detail' => '', 'qty' => 1, 'price' => 0];
    $items = old('items', $ph
        ? $ph->items->map(fn ($i) => $i->only(['item', 'description', 'product_detail', 'qty', 'price']))->values()->all()
        : [$blank]);
    $projectId = old('project_id', $ph->project_id ?? $prefillProject ?? '');
    $first = $projectMap[$projectId] ?? null;
    $payload = [
        'items' => array_values($items), 'blank' => $blank,
        'discount' => (int) old('discount', $ph->discount ?? 0),
        'tax' => old('tax_type', $ph->tax_type ?? 'NON PPN'),
        'ppnRate' => config('spk.ppn_rate'),
        'project' => $projectId,
        'client' => old('client_id', $ph->client_id ?? ($first['client_id'] ?? '')),
        'pic' => old('pic_id', $ph->pic_id ?? ($first['pic_id'] ?? '')),
        'area' => old('area', $ph->area ?? ($first['area'] ?? '')),
        'projects' => $projectMap,
    ];
@endphp
<div class="card-box">
    <h5 class="mb-4">{{ $ph ? 'Edit PH' : 'Add PH' }}</h5>

    <form method="POST" enctype="multipart/form-data" x-data="docForm({{ \Illuminate\Support\Js::from($payload) }})"
          action="{{ $ph ? route('phs.update', $ph) : route('phs.store') }}">
        @csrf
        @if ($ph) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-lg-6">
                <label class="lbl">Tanggal PH <span class="req">*</span></label>
                <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', optional($ph?->tanggal)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
            </div>
            <div class="col-lg-6">
                <label class="lbl">No PH</label>
                <input type="text" class="form-control" value="{{ $number }}" readonly>
            </div>

            <div class="col-lg-6">
                <label class="lbl">Project <span class="req">*</span></label>
                <select name="project_id" class="form-select" x-model="project" x-on:change="pickProject()">
                    <option value="">-- Pilih Project --</option>
                    @foreach ($projects as $p)
                        <option value="{{ $p->id }}">{{ $p->number }} - {{ $p->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6">
                <label class="lbl">Client <span class="req">*</span></label>
                <select name="client_id" class="form-select" x-model="client">
                    <option value="">-- Pilih Client --</option>
                    @foreach ($clients as $c)<option value="{{ $c->id }}">{{ $c->nama }}</option>@endforeach
                </select>
            </div>
            <div class="col-lg-6">
                <label class="lbl">PIC <span class="req">*</span></label>
                <select name="pic_id" class="form-select" x-model="pic">
                    <option value="">-- Pilih PIC --</option>
                    @foreach ($pics as $p)<option value="{{ $p->id }}">{{ $p->nama }}</option>@endforeach
                </select>
            </div>
            <div class="col-lg-6">
                <label class="lbl">PO Number</label>
                <input type="text" name="po_number" class="form-control" value="{{ old('po_number', $ph->po_number ?? '') }}" placeholder="Masukan PO Number (jika ada)">
            </div>
            <div class="col-lg-6">
                <label class="lbl">Area</label>
                <textarea name="area" rows="2" class="form-control" placeholder="Masukan Area" x-model="area"></textarea>
            </div>
            <div class="col-lg-6">
                <label class="lbl">Deskripsi</label>
                <textarea name="description" rows="2" class="form-control" placeholder="Masukan Deskripsi">{{ old('description', $ph->description ?? '') }}</textarea>
            </div>
        </div>

        <div class="mt-4">
            <label class="lbl">Items <span class="req">*</span></label>
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-2">
                    <thead><tr><th>Item</th><th>Description</th><th>Product Detail</th><th style="width:90px">Qty</th><th style="width:150px">Price</th><th style="width:140px">Total</th><th style="width:60px">Aksi</th></tr></thead>
                    <tbody>
                        <template x-for="(it, i) in items" x-bind:key="i">
                            <tr>
                                <td><input class="form-control form-control-sm" x-bind:name="'items['+i+'][item]'" x-model="it.item" required></td>
                                <td><input class="form-control form-control-sm" x-bind:name="'items['+i+'][description]'" x-model="it.description"></td>
                                <td><input class="form-control form-control-sm" x-bind:name="'items['+i+'][product_detail]'" x-model="it.product_detail"></td>
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
                <label class="lbl">Upload File</label>
                <input type="file" name="file" class="form-control">
                @if ($ph && $ph->file_name)<div class="small text-muted mt-1">File saat ini: {{ $ph->file_name }} (upload baru untuk mengganti)</div>@endif

                <label class="lbl mt-4">Note</label>
                <textarea name="note" rows="5" class="form-control">{{ old('note', $ph->note ?? config('decorind.ph_note')) }}</textarea>
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

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('phs.index') }}" class="btn btn-outline-secondary btn-sm px-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <button class="btn btn-primary-x btn-sm px-3" type="submit"><i class="bi bi-save"></i> Save PH</button>
        </div>
    </form>
</div>
@endsection
