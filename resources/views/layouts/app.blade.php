<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('judul') - Priangan Kilat Express</title>
  <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark px-3">
  <span class="navbar-brand">Priangan Kilat Express</span>
  @auth
    <div class="d-flex gap-3 align-items-center text-white">
      {{-- Menu: tambahkan satu tautan per bab yang selesai --}}
      @if (auth()->user()->role === 'admin')
        <a class="text-white" href="{{ route('admin.pelanggan.index') }}">Pelanggan</a>
        <a class="text-white" href="{{ route('admin.kurir.index') }}">Kurir</a>
      @elseif (auth()->user()->role === 'manajer')
        <a class="text-white" href="{{ route('manajer.pelanggan') }}">Data Pelanggan</a>
        <a class="text-white" href="{{ route('manajer.kurir') }}">Data Kurir</a>
      @endif
      <span>{{ auth()->user()->nama }} ({{ auth()->user()->role }})</span>
      <a class="btn btn-sm btn-outline-light" href="/ganti-password">Ganti password</a>
      <form method="post" action="/logout" class="m-0">@csrf<button class="btn btn-sm btn-light">Keluar</button></form>
    </div>
  @endauth
</nav>
<div class="container py-4">
  @if (session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif
  @if ($errors->any())
    <div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
  @endif
  @yield('isi')
</div>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>