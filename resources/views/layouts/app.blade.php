<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('judul') - Priangan Kilat Express</title>
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="topbar">
  <div class="brand">
    <img src="{{ asset('img/logo_icon.png') }}" width="34" height="34" alt="">
    <span>Priangan Kilat Express</span>
  </div>
  @auth
  <div class="dropdown">
    <button class="btn-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
      <span class="avatar">{{ strtoupper(mb_substr(auth()->user()->nama, 0, 1)) }}</span>
      {{ auth()->user()->nama }}
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
      <li class="px-3 py-2">
        <div class="fw-bold">{{ auth()->user()->nama }}</div>
        <div class="small text-muted">{{ '@' . auth()->user()->username }} &middot; {{ ucfirst(auth()->user()->role) }}</div>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item" href="/ganti-password">Ganti password</a></li>
      <li><form method="post" action="/logout">@csrf<button type="submit" class="dropdown-item">Keluar</button></form></li>
    </ul>
  </div>
@endauth
</header>

<div class="shell">
  @auth
  <aside class="sidebar">
    <nav>
      @if (auth()->user()->role === 'admin')
        <a class="item {{ request()->is('admin/paket*') ? 'active' : '' }}" href="{{ route('admin.paket.index') }}">Paket</a>
        <a class="item {{ request()->is('admin/penugasan*') ? 'active' : '' }}" href="{{ url('/admin/penugasan') }}">Penugasan Kurir</a>
        <a class="item {{ request()->is('admin/setoran*') ? 'active' : '' }}" href="{{ url('/admin/setoran') }}">Setoran COD</a>
        <a class="item {{ request()->is('admin/pelanggan*') ? 'active' : '' }}" href="{{ route('admin.pelanggan.index') }}">Pelanggan</a>
        <a class="item {{ request()->is('admin/kurir*') ? 'active' : '' }}" href="{{ route('admin.kurir.index') }}">Kurir</a>
        <a class="item {{ request()->is('admin/tarif*') ? 'active' : '' }}" href="{{ route('admin.tarif') }}">Tarif</a>
        <a class="item {{ request()->is('admin/impor*') ? 'active' : '' }}" href="{{ url('/admin/impor') }}">Impor CSV</a>
      @elseif (auth()->user()->role === 'manajer')
        {{-- Menu Manajer sementara: sesuaikan setelah wireframe Manajer ada --}}
        <a class="item {{ request()->is('manajer/tarif*') ? 'active' : '' }}" href="{{ route('manajer.tarif') }}">Tarif</a>
        <a class="item {{ request()->is('manajer/laporan*') ? 'active' : '' }}" href="{{ url('/manajer/laporan') }}">Laporan</a>
        <a class="item {{ request()->is('manajer/pelanggan*') ? 'active' : '' }}" href="{{ route('manajer.pelanggan') }}">Pelanggan</a>
        <a class="item {{ request()->is('manajer/kurir*') ? 'active' : '' }}" href="{{ route('manajer.kurir') }}">Kurir</a>
      @endif
    </nav>
    <div class="sidebar-bottom">
      <form method="post" action="/logout">@csrf<button type="submit">Keluar</button></form>
    </div>
  </aside>
  @endauth

  <main class="content">
    @if (session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif
    @if ($errors->any())
      <div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
    @endif
    @yield('isi')
  </main>
</div>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>