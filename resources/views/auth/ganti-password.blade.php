@extends('layouts.app')
@section('judul', 'Ganti password')
@section('isi')
<div class="card" style="max-width:420px"><div class="card-body">
  <h5 class="mb-3">Ganti password</h5>
  <form method="post" action="/ganti-password">@csrf
    <label class="form-label">Password baru (minimal 8 karakter)</label>
    <input type="password" name="password" class="form-control mb-2">
    <label class="form-label">Ulangi password baru</label>
    <input type="password" name="password_confirmation" class="form-control mb-3">
    <button class="btn btn-dark">Simpan</button>
  </form>
</div></div>
@endsection