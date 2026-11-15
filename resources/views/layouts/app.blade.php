<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('judul) - Priangan KIlat Express</title>
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark px-3>
    <span class="navbar-brand">Priangan Kilat Express</span>
    @auth
    <div class="d-flex gap-2 align-items-center text-white">
        <span>{{ auth()->user()->nama }} ({{ auth()->user()->role }})</span>
        <a class="btn btn-sm btn-outline-light" href="/ganti-password">Ganti password</a>
        <form method="post" action="/logout">@csrf<button class="btn btn-sm btn-light">Keluar</button></form>
    </div>
    @endauth
</nav>
    <div class="container py-4">
        @if (session('ok')) <div class="alert alert-succes">{{ session('ok') }}</div> @endif
        @if ($errors->any()) <div class="alert alert-danger">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div> @endif
        @yield('isi')
    </div>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    
</body>
</html>