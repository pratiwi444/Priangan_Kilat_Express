@extends('layouts.app')
@section('judul', 'Tambah paket')
@section('isi')
<div class="card" style="max-width:640px"><div class="card-body">
  <h5 class="mb-3">Tambah paket</h5>
  <form method="post" action="{{ route('admin.paket.store') }}">
    @csrf

    <label class="form-label">Pengirim</label>
    <select name="pelanggan_id" class="form-select mb-2">
      <option value="">Pilih pelanggan</option>
      @foreach ($pelanggan as $p)
        <option value="{{ $p->id }}" @selected(old('pelanggan_id') == $p->id)>{{ $p->nama }} ({{ $p->telepon }})</option>
      @endforeach
    </select>

    <div class="row g-2 mb-2">
      <div class="col-md-6">
        <label class="form-label">Nama penerima</label>
        <input name="nama_penerima" class="form-control" value="{{ old('nama_penerima') }}">
      </div>
      <div class="col-md-6">
        <label class="form-label">Telepon penerima</label>
        <input name="telepon_penerima" class="form-control" value="{{ old('telepon_penerima') }}">
      </div>
    </div>

    <label class="form-label">Kota tujuan</label>
    <input name="kota_tujuan" class="form-control mb-2" value="{{ old('kota_tujuan') }}">

    <div class="row g-2 mb-2">
      <div class="col-md-6">
        <label class="form-label">Berat (kg)</label>
        <input name="berat" type="number" step="0.01" min="0" class="form-control" value="{{ old('berat') }}">
      </div>
      <div class="col-md-6">
        <label class="form-label">Layanan</label>
        <select name="layanan" class="form-select">
          <option value="reguler" @selected(old('layanan', 'reguler') === 'reguler')>Reguler</option>
          <option value="express" @selected(old('layanan') === 'express')>Express</option>
        </select>
      </div>
    </div>

    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" name="is_cod" value="1" id="is_cod" @checked(old('is_cod'))>
      <label class="form-check-label" for="is_cod">Paket COD</label>
    </div>
    <label class="form-label">Nilai COD (Rp)</label>
    <input name="nilai_cod" type="number" min="1" class="form-control mb-2" value="{{ old('nilai_cod') }}">

    <label class="form-label">Zona manual</label>
    <select name="zona_id_manual" class="form-select">
      <option value="">-</option>
      @foreach ($zona as $z)
        <option value="{{ $z->id }}" @selected(old('zona_id_manual') == $z->id)>{{ $z->nama }}</option>
      @endforeach
    </select>
    <div class="form-text mb-3">Isi hanya bila zona kota tujuan tidak ditemukan otomatis.</div>

    <button class="btn btn-dark">Simpan</button>
    <a href="{{ route('admin.paket.index') }}" class="btn btn-link">Batal</a>
  </form>
</div></div>
@endsection