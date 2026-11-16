@extends('layouts.app')
@section('judul', 'Paket')
@section('isi')
@php
  $warna = ['diterima'=>'secondary','transit'=>'info','dikirim'=>'primary','terkirim'=>'success','gagal_kirim'=>'warning','retur'=>'danger'];
@endphp
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="m-0">Paket</h4>
  <a href="{{ route('admin.paket.create') }}" class="btn btn-dark">Tambah</a>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
  @foreach ($status as $s)
    <span class="badge bg-{{ $warna[$s->value] }}">{{ $s->label() }}: {{ $ringkas[$s->value] ?? 0 }}</span>
  @endforeach
</div>

<form method="get" class="row g-2 mb-3">
  <div class="col-md-4"><input name="cari" class="form-control" placeholder="Cari resi atau nama penerima" value="{{ request('cari') }}"></div>
  <div class="col-md-3">
    <select name="status" class="form-select">
      <option value="">Semua status</option>
      @foreach ($status as $s)
        <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-auto"><button class="btn btn-outline-dark">Cari</button></div>
</form>

<table class="table table-sm table-bordered bg-white align-middle">
  <thead>
    <tr><th>Resi</th><th>Masuk</th><th>Penerima</th><th>Kota tujuan</th><th>Zona</th><th>Layanan</th><th>Status</th><th>Kurir</th></tr>
  </thead>
  <tbody>
  @forelse ($paket as $p)
    <tr>
      <td><a href="{{ route('admin.paket.show', $p) }}">{{ $p->no_resi }}</a></td>
      <td>{{ $p->tanggal_masuk->format('d/m/Y') }}</td>
      <td>{{ $p->nama_penerima }}</td>
      <td>{{ $p->kota_tujuan }}</td>
      <td>{{ $p->zona?->nama }}@if ($p->zona_manual) <span class="badge bg-warning text-dark">manual</span>@endif</td>
      <td>{{ ucfirst($p->layanan) }}</td>
      <td><span class="badge bg-{{ $warna[$p->status->value] }}">{{ $p->status->label() }}</span></td>
      <td>{{ $p->kurir?->kode_kurir ?? '-' }}</td>
    </tr>
  @empty
    <tr><td colspan="8" class="text-center text-muted">Belum ada paket.</td></tr>
  @endforelse
  </tbody>
</table>
{{ $paket->links() }}
@endsection