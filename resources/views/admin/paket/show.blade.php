@extends('layouts.app')
@use('App\Enums\StatusPaket')
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
        <tr><td>Alamat</td><td>{{ $paket->alamat_penerima ?? '-' }}</td></tr>
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

    <div class="card mt-3"><div class="card-body">
  <h6>Ubah status</h6>
  @php
    $opsi = [];
    if ($paket->status === StatusPaket::Diterima) $opsi = [StatusPaket::Transit];
    elseif ($paket->status === StatusPaket::GagalKirim && $paket->jumlah_gagal >= 2) $opsi = [StatusPaket::Retur];
  @endphp
  @if ($opsi)
    <form method="post" action="{{ route('admin.paket.status', $paket) }}" class="row g-2">
      @csrf
      <div class="col-md-4">
        <select name="status" class="form-select">
          @foreach ($opsi as $o)<option value="{{ $o->value }}">{{ $o->label() }}</option>@endforeach
        </select>
      </div>
      <div class="col-md-6"><input name="catatan" class="form-control" placeholder="Catatan (opsional)"></div>
      <div class="col-auto"><button class="btn btn-dark">Simpan</button></div>
    </form>
  @else
    <p class="text-muted mb-0">
      @if ($paket->status->final()) Status sudah final.
      @elseif ($paket->status === StatusPaket::Dikirim) Status diubah oleh kurir lewat APK.
      @else Paket ini ditugaskan ke kurir lewat menu Penugasan Kurir (bab 7).
      @endif
    </p>
  @endif
</div></div>

@if ($paket->status === StatusPaket::Transit || ($paket->status === StatusPaket::GagalKirim && $paket->jumlah_gagal < 2))
<div class="card mt-3"><div class="card-body">
  <h6>Tugaskan ke kurir</h6>
  <form method="post" action="{{ route('admin.paket.tugaskan', $paket) }}" class="row g-2">
    @csrf
    <div class="col-md-6">
      <select name="kurir_id" class="form-select">
        <option value="">Pilih kurir</option>
        @foreach ($kurir as $k)
          <option value="{{ $k->id }}">{{ $k->kode_kurir }} - {{ $k->nama }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-auto"><button class="btn btn-dark">Tugaskan ke Kurir</button></div>
  </form>
</div></div>
@endif

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