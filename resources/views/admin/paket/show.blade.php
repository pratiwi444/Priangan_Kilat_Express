@extends('layouts.app')
@section('judul', 'Paket ' . $paket->no_resi)
@section('isi')
@php
  $rp = fn ($n) => 'Rp' . number_format($n, 0, ',', '.');
  $warna = ['diterima'=>'secondary','transit'=>'info','dikirim'=>'primary','terkirim'=>'success','gagal_kirim'=>'warning','retur'=>'danger'];
@endphp
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="m-0">{{ $paket->no_resi }}
    <span class="badge bg-{{ $warna[$paket->status->value] }} fs-6">{{ $paket->status->label() }}</span>
  </h4>
  <a href="{{ route('admin.paket.index') }}" class="btn btn-outline-dark">Kembali</a>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card"><div class="card-body">
      <h6>Data paket</h6>
      <table class="table table-sm mb-0">
        <tr><td>Tanggal masuk</td><td>{{ $paket->tanggal_masuk->format('d/m/Y') }}</td></tr>
        <tr><td>Pengirim</td><td>{{ $paket->pelanggan?->nama }} ({{ $paket->pelanggan?->telepon }})</td></tr>
        <tr><td>Penerima</td><td>{{ $paket->nama_penerima }} ({{ $paket->telepon_penerima }})</td></tr>
        <tr><td>Kota tujuan</td><td>{{ $paket->kota_tujuan }}</td></tr>
        <tr><td>Zona</td><td>{{ $paket->zona?->nama }}@if ($paket->zona_manual) <span class="badge bg-warning text-dark">zona manual</span>@endif</td></tr>
        <tr><td>Berat</td><td>{{ $paket->berat !== null ? $paket->berat . ' kg' : '-' }}@if ($paket->perlu_verifikasi) <span class="badge bg-danger">perlu verifikasi</span>@endif</td></tr>
        <tr><td>Layanan</td><td>{{ ucfirst($paket->layanan) }}</td></tr>
        <tr><td>Kurir</td><td>{{ $paket->kurir ? $paket->kurir->kode_kurir . ' - ' . $paket->kurir->nama : '-' }}</td></tr>
        <tr><td>Jumlah gagal kirim</td><td>{{ $paket->jumlah_gagal }}</td></tr>
        <tr><td>Alamat</td><td>{ $paket->alamat_penerima ?? '_' }</td></tr>
      </table>
    </div></div>
  </div>

  <div class="col-lg-6">
    <div class="card"><div class="card-body">
      <h6>Biaya (dibayar pengirim di loket)</h6>
      <table class="table table-sm mb-0">
        <tr><td>Tarif per kg (saat transaksi)</td><td class="text-end">{{ $rp($paket->tarif_per_kg) }}</td></tr>
        <tr><td>Ongkir</td><td class="text-end">{{ $rp($paket->ongkir) }}</td></tr>
        <tr><td>Biaya COD</td><td class="text-end">{{ $rp($paket->biaya_cod) }}</td></tr>
        <tr class="fw-bold"><td>Total</td><td class="text-end">{{ $rp($paket->ongkir + $paket->biaya_cod) }}</td></tr>
        @if ($paket->is_cod)
          <tr><td>Nilai COD (ditagih kurir ke penerima)</td><td class="text-end">{{ $rp($paket->nilai_cod) }}</td></tr>
        @endif
      </table>
    </div></div>
  </div>
</div>

{{-- Bab 6: tombol ubah status. Bab 7: form tugaskan ke kurir. Bab 9: setoran COD. --}}

<div class="card mt-3"><div class="card-body">
  <h6>Riwayat status</h6>
  <table class="table table-sm mb-0">
    <thead><tr><th>Waktu</th><th>Perubahan</th><th>Pelaku</th><th>Catatan</th></tr></thead>
    <tbody>
    @foreach ($paket->riwayat as $h)
      <tr>
        <td>{{ $h->waktu->format('d/m/Y H:i') }}</td>
        <td>{{ $h->status_dari?->label() ?? 'Baru' }} &rarr; {{ $h->status_ke->label() }}</td>
        <td>{{ $h->pelaku?->nama }} ({{ $h->pelaku?->role }})</td>
        <td>{{ $h->catatan }}</td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div></div>
@endsection