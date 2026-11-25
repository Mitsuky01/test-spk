@extends('layouts.app')
@section('title', $project ? 'Edit Project' : 'Add Project')
@section('content')
<div class="card-box">
    <h5 class="mb-4">{{ $project ? 'Edit Project' : 'Add Project' }}</h5>

    <form method="POST" action="{{ $project ? route('projects.update', $project) : route('projects.store') }}">
        @csrf
        @if ($project) @method('PUT') @endif

        <div class="row g-3">
            <div class="col-lg-6">
                <label class="lbl">No Project</label>
                <input type="text" class="form-control" value="{{ $number }}" readonly>
            </div>
            <div class="col-lg-6">
                <label class="lbl">Nama Project <span class="req">*</span></label>
                <input type="text" name="nama" class="form-control" value="{{ old('nama', $project->nama ?? '') }}" placeholder="Masukan Nama Project">
            </div>
            <div class="col-lg-6">
                <label class="lbl">Client <span class="req">*</span></label>
                <select name="client_id" class="form-select">
                    <option value="">-- Pilih Client --</option>
                    @foreach ($clients as $c)
                        <option value="{{ $c->id }}" @selected(old('client_id', $project->client_id ?? '') == $c->id)>{{ $c->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6">
                <label class="lbl">PIC <span class="req">*</span></label>
                <select name="pic_id" class="form-select">
                    <option value="">-- Pilih PIC --</option>
                    @foreach ($pics as $p)
                        <option value="{{ $p->id }}" @selected(old('pic_id', $project->pic_id ?? '') == $p->id)>{{ $p->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6">
                <label class="lbl">Area <span class="req">*</span></label>
                <input type="text" name="area" class="form-control" value="{{ old('area', $project->area ?? '') }}" placeholder="Masukan Area">
            </div>
            <div class="col-lg-6">
                <label class="lbl">Deskripsi</label>
                <textarea name="description" rows="2" class="form-control" placeholder="Masukan Deskripsi Project">{{ old('description', $project->description ?? '') }}</textarea>
            </div>
            @if ($project)
                <div class="col-lg-6">
                    <label class="lbl">Status</label>
                    <select name="status" class="form-select">
                        @foreach (\App\Models\Project::STATUS as $k => $label)
                            <option value="{{ $k }}" @selected(old('status', $project->status) === $k)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary btn-sm px-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <button class="btn btn-primary-x btn-sm px-3" type="submit"><i class="bi bi-save"></i> Save Project</button>
        </div>
    </form>
</div>
@endsection
