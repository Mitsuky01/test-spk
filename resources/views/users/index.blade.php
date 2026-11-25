@extends('layouts.app')
@section('title', 'User')
@section('content')
<div class="card-box">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="m-0">User Management</h5>
        <a href="{{ route('users.create') }}" class="btn btn-primary-x"><i class="bi bi-plus-lg"></i> Add User</a>
    </div>
    <div class="table-responsive table-wrap">
        <table class="table table-bordered mb-0">
            <thead><tr><th>No</th><th>Nama</th><th>Email</th><th>Created Date</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach ($users as $u)
                <tr>
                    <td class="mid">{{ $users->firstItem() + $loop->index }}</td>
                    <td class="mid">{{ $u->name }}</td>
                    <td class="mid">{{ $u->email }}</td>
                    <td class="mid">{{ $u->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="mid">
                        @if ($u->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $u) }}" onsubmit="return confirm('Hapus user ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger-x"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        @else
                            <span class="text-muted small">akun Anda</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center mt-3">{{ $users->links() }}</div>
</div>
@endsection
