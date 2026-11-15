@extends('layouts.app')
@section('judul', $p->exists ? 'Ubah pelanggan' : 'Tambah pelanggan')
@section('isi')
<div class="card" style="max-width:480px"><div class="card-body">
  <h5 class="mb-3">{{ $p->exists ? 'Ubah' : 'Tambah' }} pelanggan</h5>
  <form method="post" action="{{ $p->exists ? route('admin.pelanggan.update', $p) : route('admin.pelanggan.store') }}">
    @csrf
    @if ($p->exists) @method('PUT') @endif
    <label class="form-label">Nama</label>
    <input name="nama" class="form-control mb-2" value="{{ old('nama', $p->nama) }}">
    <label class="form-label">Telepon</label>
    <input name="telepon" class="form-control mb-3" value="{{ old('telepon', $p->telepon) }}">
    <button class="btn btn-dark">Simpan</button>
    <a href="{{ route('admin.pelanggan.index') }}" class="btn btn-link">Batal</a>
  </form>
</div></div>
@endsection