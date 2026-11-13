<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · Priangan Kilat Express</title>
    <style>
        :root { --navy:#2c3e50; --sky:#7ecdf4; --blue:#2484c4; --orange:#ff9f1c; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; display:grid; place-items:center;
               font-family:system-ui,Segoe UI,Arial,sans-serif; background:#eef6fb; }
        .kartu { width:100%; max-width:400px; background:#fff; padding:32px;
                 border-radius:16px; box-shadow:0 8px 30px rgba(44,62,80,.12); }
        .kartu img { display:block; width:240px; margin:0 auto 24px; }
        label { display:block; font-weight:600; color:var(--navy); margin:14px 0 6px; }
        input { width:100%; padding:11px 12px; border:1px solid #c5d3df; border-radius:8px; font-size:15px; }
        input:focus { outline:2px solid var(--sky); border-color:var(--blue); }
        button { width:100%; margin-top:22px; padding:12px; border:0; border-radius:8px;
                 background:var(--navy); color:#fff; font-size:16px; font-weight:700; cursor:pointer; }
        button:hover { background:#34495e; }
        .error { background:#fdecea; color:#b3261e; padding:10px 12px; border-radius:8px; margin-bottom:6px; font-size:14px; }
        .error-kecil { color:#b3261e; font-size:13px; margin-top:4px; }
    </style>
</head>
<body>
    <form class="kartu" method="POST" action="{{ route('login.proses') }}">
        @csrf
        <img src="{{ asset('img/logo.png') }}" alt="Priangan Kilat Express">

        @error('login') <div class="error">{{ $message }}</div> @enderror

        <label for="username">Username</label>
        <input id="username" name="username" value="{{ old('username') }}" autofocus autocomplete="username">
        @error('username') <div class="error-kecil">{{ $message }}</div> @enderror

        <label for="password">Password</label>
        <input id="password" type="password" name="password" autocomplete="current-password">
        @error('password') <div class="error-kecil">{{ $message }}</div> @enderror

        <button type="submit">Masuk</button>
    </form>
</body>
</html>