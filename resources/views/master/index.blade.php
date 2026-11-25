@extends('layouts.app')
@section('title', $c['title'] . ' Management')
@section('content')
<div class="card-box">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <h5 class="m-0">{{ $c['title'] }} Management</h5>
        <div class="d-flex gap-2 flex-wrap">
            <form method="GET" class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ $c['placeholder'] }}">
            </form>
            <a href="{{ route($c['route'] . '.create') }}" class="btn btn-primary-x"><i class="bi bi-plus-lg"></i> Add {{ $c['title'] }}</a>
        </div>
    </div>

    <div class="mb-2">
        <a href="{{ route($c['route'] . '.export') }}"><i class="bi bi-download"></i> Download</a>
        <a href="{{ request()->fullUrl() }}" class="ms-3"><i class="bi bi-arrow-repeat"></i> Sync Now</a>
    </div>

    <div class="table-responsive table-wrap">
        <table class="table table-bordered mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    @foreach ($c['columns'] as $label)<th>{{ $label }}</th>@endforeach
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="mid">{{ $rows->firstItem() + $loop->index }}</td>
                    @foreach ($c['columns'] as $key => $label)
                        <td class="mid">
                            @if ($key === 'created_at')
                                {{ $row->created_at?->format('d/m/Y H:i') }}
                            @elseif ($loop->first)
                                <a href="{{ route($c['route'] . '.edit', $row->id) }}">{{ $row->{$key} }}</a>
                            @else
                                {{ $row->{$key} ?: '-' }}
                            @endif
                        </td>
                    @endforeach
                    <td class="mid text-nowrap">
                        <a href="{{ route($c['route'] . '.edit', $row->id) }}" class="btn btn-sm btn-warning-x"><i class="bi bi-pencil"></i> Edit</a>
                        <form method="POST" action="{{ route($c['route'] . '.destroy', $row->id) }}" class="d-inline"
                              onsubmit="return confirm('Hapus data ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger-x"><i class="bi bi-trash"></i> Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="{{ count($c['columns']) + 2 }}" class="text-center text-muted py-4">Belum ada data.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">{{ $rows->links() }}</div>
</div>
@endsection
