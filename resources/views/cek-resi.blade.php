@extends('layouts.app')
@section('judul', 'Cek Resi')
@section('isi')
<div class="mx-auto" style="max-width:680px">
  <h4 class="mb-3">Cek Resi</h4>
  @guest <a href="{{ route('login') }}" class="small">Masuk untuk petugas</a> @endguest

  <form method="get" action="{{ route('cek-resi') }}" class="d-flex gap-2 mb-3">
    <input name="resi" class="form-control" placeholder="Contoh: PKE-261116-0001" value="{{ request('resi') }}">
    <button class="btn btn-dark">Cek</button>
  </form>

  @if ($tidakAda)
    <div class="alert alert-warning">Paket tidak ditemukan.</div>
  @endif

  @if ($hasil)
    <div class="card mb-3"><div class="card-body">
      <div class="fw-bold fs-5">{{ $hasil['no_resi'] }}</div>
      <div>Status: <strong>{{ $hasil['status'] }}</strong></div>
      <div>Telepon penerima: {{ $hasil['telepon'] }}</div>
    </div></div>

    <table class="table table-sm table-bordered bg-white">
      <thead><tr><th>Waktu</th><th>Status</th><th>Catatan</th></tr></thead>
      <tbody>
      @foreach ($hasil['riwayat'] as $h)
        <tr><td>{{ $h['waktu'] }}</td><td>{{ $h['status'] }}</td><td>{{ $h['catatan'] ?? '-' }}</td></tr>
      @endforeach
      </tbody>
    </table>
  @endif
</div>
@endsection