<style>
    :root { --primary:#8a3ffc; --primary-dark:#6f2fd6; --bg:#f5f6fa; --line:#e4e6ef; }
    body { font-family:'Mulish',sans-serif; background:var(--bg); color:#3b3d45; font-size:14px; }
    a { color:#4b4fd8; }
    .text-primary-x { color:var(--primary); }

    .sidebar { position:fixed; inset:0 auto 0 0; width:240px; background:#fff; box-shadow:0 0 24px rgba(0,0,0,.05); overflow-y:auto; z-index:1030; transition:transform .2s; }
    .main { margin-left:240px; min-height:100vh; display:flex; flex-direction:column; transition:margin .2s; }
    body.side-closed .sidebar { transform:translateX(-100%); }
    body.side-closed .main { margin-left:0; }
    @media (max-width:991px) {
        .sidebar { transform:translateX(-100%); }
        .main { margin-left:0; }
        body.side-open .sidebar { transform:none; }
    }

    .logo { display:flex; align-items:center; gap:6px; padding:18px 20px 8px; }
    .logo span { font-size:24px; font-weight:700; letter-spacing:1px; color:#111; }
    .logo-dot { width:22px; height:22px; border-radius:50%; background:conic-gradient(#f5a623,#d9467a,#7b3fa0,#31a3b8,#f5a623); }

    .menu-item { display:flex; align-items:center; gap:14px; padding:11px 22px; color:#1d3b34; text-decoration:none; font-weight:600; }
    .menu-item i:first-child { color:#7aa7c7; font-size:17px; }
    .menu-item .caret { margin-left:auto; font-size:10px; color:#999; transition:transform .15s; }
    .menu-item[aria-expanded="true"] .caret { transform:rotate(90deg); }
    .sub-item { display:block; padding:8px 22px 8px 56px; color:#8b93a7; text-decoration:none; }
    .sub-item:hover, .sub-item.active { color:var(--primary); }

    .topbar { height:68px; background:var(--bg); border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; padding:0 22px; }
    .avatar { width:36px; height:36px; border-radius:50%; background:#1f6f78; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-weight:700; }
    .content { flex:1; padding:26px 24px; }
    .footer { text-align:center; padding:32px 12px; color:#777; font-size:12px; }

    .card-box { background:#fff; border-radius:14px; padding:26px 28px; box-shadow:0 1px 3px rgba(0,0,0,.03); }
    .card-box > h5, .card-box h5.title { font-weight:700; font-size:16px; color:#444; }

    .btn-primary-x { background:var(--primary); border-color:var(--primary); color:#fff; }
    .btn-primary-x:hover { background:var(--primary-dark); border-color:var(--primary-dark); color:#fff; }
    .btn-warning-x { background:#ffc107; border-color:#ffc107; color:#222; font-size:12px; font-weight:600; }
    .btn-danger-x { background:#dc3545; border-color:#dc3545; color:#fff; font-size:12px; font-weight:600; }

    .form-control, .form-select { border-radius:10px; border-color:#dfe3f5; padding:.6rem .9rem; }
    .form-control:focus, .form-select:focus { border-color:var(--primary); box-shadow:0 0 0 .15rem rgba(138,63,252,.15); }
    .form-control[readonly], .form-control:disabled { background:#e9ecef; }
    label.lbl { font-weight:600; margin-bottom:4px; }
    .req { color:#dc3545; }

    .search-box { display:flex; align-items:center; gap:8px; border:1px solid #5a4fcf; border-radius:8px; padding:0 12px; background:#fff; min-width:300px; }
    .search-box input, .search-box select { border:0; outline:0; padding:9px 0; width:100%; background:transparent; font-size:13px; }

    .table-wrap { box-shadow:0 2px 6px rgba(0,0,0,.12); border-radius:4px; }
    .table-wrap table { font-size:12.5px; }
    .table-wrap thead th { background:linear-gradient(#fff,#ececec); font-weight:700; white-space:nowrap; }
    .table-wrap td { vertical-align:top; }
    .table-wrap td.mid { vertical-align:middle; }

    .pill { display:inline-block; border-radius:20px; padding:2px 12px; font-size:11px; font-weight:700; color:#fff; white-space:nowrap; }
    .pill-green { background:#17c964; } .pill-orange { background:#f5a524; } .pill-red { background:#f31260; }
    .pill-blue { background:#0072f5; } .pill-purple { background:#7828c8; } .pill-grey { background:#889096; }
    .pill-soft { background:#a8f0c6; color:#0a6b35; }

    .sum-main { background:linear-gradient(135deg,#8a3ffc,#6a2ccf); color:#fff; border-radius:12px; padding:18px 22px; text-align:right; }
    .sum-main .big { font-size:22px; font-weight:700; }
    .sum-box { background:#5e4b87; color:#fff; border-radius:12px; padding:16px 18px; text-align:right; height:100%; }
    .sum-box .big { font-size:15px; font-weight:700; }
    .sum-main small, .sum-box small { opacity:.75; display:block; }
    .sum-wrap { border:1px solid var(--line); border-radius:8px; padding:12px; margin-bottom:22px; }

    .progress { height:8px; }
    .calc-box { border:1px solid #dfe3f5; border-radius:14px; padding:0 0 12px; }
    .calc-box .head { background:#1fddff; color:#fff; border-radius:12px; padding:8px 12px; margin:6px; }
    .calc-box .body { padding:6px 16px; }

    .pagination { --bs-pagination-active-bg:var(--primary); --bs-pagination-active-border-color:var(--primary); }
</style>
