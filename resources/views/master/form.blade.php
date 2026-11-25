@extends('layouts.app')
@section('title', ($row ? 'Edit ' : 'Add ') . $c['title'])
@section('content')
<div class="card-box">
    <h5 class="mb-4">{{ $row ? 'Edit' : 'Add New' }} {{ $c['title'] }}</h5>

    <form method="POST" action="{{ $row ? route($c['route'] . '.update', $row->id) : route($c['route'] . '.store') }}">
        @csrf
        @if ($row) @method('PUT') @endif

        <div class="row g-3">
            @foreach ($c['fields'] as $key => [$label, $required, $col])
                <div class="col-lg-{{ $col }}">
                    <label class="lbl" for="f_{{ $key }}">{{ $label }} @if ($required)<span class="req">*</span>@endif</label>
                    <input id="f_{{ $key }}" type="text" name="{{ $key }}" class="form-control @error($key) is-invalid @enderror"
                           value="{{ old($key, $row->{$key} ?? '') }}" placeholder="Masukan {{ $label }}">
                    @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route($c['route'] . '.index') }}" class="btn btn-outline-secondary btn-sm px-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <button class="btn btn-primary-x btn-sm px-3" type="submit"><i class="bi bi-save"></i> Save {{ $c['title'] }}</button>
        </div>
    </form>
</div>
@endsection
