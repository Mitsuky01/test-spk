@extends('layouts.app')
@section('title', 'Add User')
@section('content')
<div class="card-box">
    <h5 class="mb-4">Add User</h5>
    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-lg-6">
                <label class="lbl">Nama <span class="req">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Masukan Nama">
            </div>
            <div class="col-lg-6">
                <label class="lbl">Email <span class="req">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Masukan Email">
            </div>
            <div class="col-lg-6">
                <label class="lbl">Password <span class="req">*</span></label>
                <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter">
            </div>
            <div class="col-lg-6">
                <label class="lbl">Konfirmasi Password <span class="req">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password">
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm px-3"><i class="bi bi-arrow-left"></i> Kembali</a>
            <button class="btn btn-primary-x btn-sm px-3" type="submit"><i class="bi bi-save"></i> Save User</button>
        </div>
    </form>
</div>
@endsection
