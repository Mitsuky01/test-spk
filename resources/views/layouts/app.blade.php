<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Decorind') - Decorind</title>
    <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    @include('partials.style')
</head>
<body>
@php
    $menu = [
        ['Client', 'bi-person-lines-fill', ['clients.*'], [
            ['Client List', 'clients.index', []], ['Add New Client', 'clients.create', []]]],
        ['PIC', 'bi-person', ['pics.*'], [
            ['PIC List', 'pics.index', []], ['Add New PIC', 'pics.create', []]]],
        ['Vendor', 'bi-code-square', ['vendors.*'], [
            ['Vendor List', 'vendors.index', []], ['Add New Vendor', 'vendors.create', []]]],
        ['Project', 'bi-grid-1x2', ['projects.*'], [
            ['Project List', 'projects.index', ['status' => 'open']],
            ['Project Done', 'projects.index', ['status' => 'done']],
            ['Project Canceled', 'projects.index', ['status' => 'canceled']],
            ['Add New Project', 'projects.create', []]]],
        ['Dashboard', 'bi-graph-up', ['spks.*', 'phs.*', 'invoice', 'po'], [
            ['SPK', 'spks.index', []], ['Add SPK', 'spks.create', []],
            ['PH', 'phs.index', []], ['Add PH', 'phs.create', []],
            ['Invoice', 'invoice', []], ['PO', 'po', []]]],
        ['User', 'bi-person-circle', ['users.*'], [
            ['User List', 'users.index', []], ['Add User', 'users.create', []]]],
    ];
    $current = request()->route()?->getName();
@endphp

<aside class="sidebar">
    <div class="logo"><span>DECORIND</span><i class="logo-dot"></i></div>
    <nav class="mt-3">
        @foreach ($menu as $i => [$label, $icon, $patterns, $children])
            @php $open = request()->routeIs(...$patterns); @endphp
            <a class="menu-item" data-bs-toggle="collapse" href="#m{{ $i }}" role="button"
               aria-expanded="{{ $open ? 'true' : 'false' }}">
                <i class="bi {{ $icon }}"></i><span>{{ $label }}</span><i class="bi bi-chevron-right caret"></i>
            </a>
            <div class="collapse {{ $open ? 'show' : '' }}" id="m{{ $i }}">
                @foreach ($children as [$childLabel, $routeName, $params])
                    @php
                        $active = $current === $routeName
                            && (! isset($params['status']) || request('status', 'open') === $params['status']);
                    @endphp
                    <a class="sub-item {{ $active ? 'active' : '' }}" href="{{ route($routeName, $params) }}">{{ $childLabel }}</a>
                @endforeach
            </div>
        @endforeach
    </nav>
</aside>

<div class="main">
    <header class="topbar">
        <button class="btn btn-link p-0 text-decoration-none" id="sideToggle" type="button"><i class="bi bi-list fs-2 text-primary-x"></i></button>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none" data-bs-toggle="dropdown">
                <span class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li class="dropdown-header">{{ auth()->user()->name }}<br><small>{{ auth()->user()->email }}</small></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </header>

    <main class="content">
        @if (session('ok'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('ok') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if (session('err'))
            <div class="alert alert-danger alert-dismissible fade show">{{ session('err') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Periksa kembali isian Anda:</strong>
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="footer">{{ config('decorind.footer') }}</footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('sideToggle').addEventListener('click', function () {
        document.body.classList.toggle(window.innerWidth < 992 ? 'side-open' : 'side-closed');
    });

    // Form dokumen (PH & SPK): item dinamis + hitung total langsung di browser.
    // Angka final tetap dihitung ulang di server saat disimpan.
    document.addEventListener('alpine:init', function () {
        Alpine.data('docForm', function (init) {
            return {
                items: init.items, discount: init.discount, tax: init.tax, ppnRate: init.ppnRate,
                project: init.project, client: init.client, pic: init.pic, area: init.area,
                vendor: init.vendor, ttd1: init.ttd1,
                projects: init.projects || {}, vendors: init.vendors || {}, picNames: init.picNames || {},

                get subtotal() { return this.items.reduce(function (s, i) { return s + (Number(i.qty) || 0) * (Number(i.price) || 0); }, 0); },
                get net() { return Math.max(0, this.subtotal - (Number(this.discount) || 0)); },
                get taxAmount() { return this.tax === 'PPN' ? Math.round(this.net * this.ppnRate / 100) : 0; },
                get grand() { return this.net + this.taxAmount; },
                get v() { return this.vendors[this.vendor] || {}; },

                rp: function (n) { return new Intl.NumberFormat('id-ID').format(n || 0); },
                add: function () { this.items.push(JSON.parse(JSON.stringify(init.blank))); },
                remove: function (i) { if (this.items.length > 1) this.items.splice(i, 1); },
                rowTotal: function (it) { return (Number(it.qty) || 0) * (Number(it.price) || 0); },
                syncTtd: function () { if (this.picNames[this.pic]) this.ttd1 = this.picNames[this.pic]; },
                pickProject: function () {
                    var p = this.projects[this.project];
                    if (!p) return;
                    this.client = p.client_id; this.pic = p.pic_id; this.area = p.area || '';
                    this.syncTtd();
                }
            };
        });
    });
</script>
@stack('scripts')
</body>
</html>
