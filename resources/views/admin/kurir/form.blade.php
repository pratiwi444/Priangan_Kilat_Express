@extends('layouts.app')
@section('judul', $k->exists ? 'Ubah kurir' : 'Tambah kurir')
@section('isi')
<div class="card" style="max-width:480px"><div class="card-body">
  <h5 class="mb-3">{{ $k->exists ? 'Ubah' : 'Tambah' }} kurir</h5>
  <form method="post" action="{{ $k->exists ? route('admin.kurir.update', $k) : route('admin.kurir.store') }}">
    @csrf
    @if ($k->exists) @method('PUT') @endif

    <label class="form-label">Kode kurir</label>
    @if ($k->exists)
      <input class="form-control mb-2" value="{{ $k->kode_kurir }}" disabled>
    @else
      <input name="kode_kurir" class="form-control mb-2" placeholder="mis. KUR-01" value="{{ old('kode_kurir') }}">
    @endif

    <label class="form-label">Nama</label>
    <input name="nama" class="form-control mb-2" value="{{ old('nama', $k->nama) }}">

    <label class="form-label">Telepon</label>
    <input name="telepon" class="form-control mb-2" value="{{ old('telepon', $k->telepon) }}">

    @unless ($k->exists)
      <label class="form-label">Password sementara (minimal 8 karakter)</label>
      <input name="password" class="form-control mb-3" value="{{ old('password') }}">
    @endunless

    <button class="btn btn-dark">Simpan</button>
    <a href="{{ route('admin.kurir.index') }}" class="btn btn-link">Batal</a>
  </form>
</div></div>
@endsection