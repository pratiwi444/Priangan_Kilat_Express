@extends('layouts.app')
@section('judul', 'Setoran COD')
@section('isi')
<h4 class="mb-3">Setoran COD</h4>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <form method="get" action="{{ route('admin.setoran.index') }}">
      <label class="form-label fw-bold">Kurir</label>
      <select name="kurir_id" class="form-select" onchange="this.form.submit()">
        <option value="">Semua kurir</option>
        @foreach ($kurir as $k)
          <option value="{{ $k->id }}" @selected(request('kurir_id') == $k->id)>{{ $k->kode_kurir }} - {{ $k->nama }}</option>
        @endforeach
      </select>
    </form>
  </div>
  <div class="col-md-8">
    <div class="border rounded p-3 bg-white d-flex justify-content-between align-items-center h-100">
      <span class="text-muted">Total COD belum disetor</span>
      <span class="fs-4 fw-bold">Rp{{ number_format($belum, 0, ',', '.') }}</span>
    </div>
  </div>
</div>

<h6>Paket COD Terkirim</h6>
<form method="post" action="{{ route('admin.setoran.store') }}">
  @csrf
  <table class="table table-sm table-bordered bg-white align-middle">
    <thead>
      <tr><th style="width:40px"></th><th>No. Resi</th><th>Kurir</th><th>Penerima</th><th class="text-end">Nilai COD</th><th>Status</th></tr>
    </thead>
    <tbody>
    @forelse ($paket as $p)
      <tr>
        <td><input type="checkbox" name="paket_id[]" value="{{ $p->id }}" @disabled($p->setoran)></td>
        <td>{{ $p->no_resi }}</td>
        <td>{{ $p->kurir?->kode_kurir ?? '-' }}</td>
        <td>{{ $p->nama_penerima }}</td>
        <td class="text-end">Rp{{ number_format($p->nilai_cod, 0, ',', '.') }}</td>
        <td>
          @if ($p->setoran) <span class="badge bg-success">Sudah disetor</span>
          @else <span class="badge bg-warning text-dark">Belum disetor</span>
          @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="6" class="text-center text-muted">Belum ada paket COD Terkirim.</td></tr>
    @endforelse
    </tbody>
  </table>
  @if ($paket->count())
    <div class="text-end"><button class="btn btn-dark">Catat Setoran</button></div>
  @endif
</form>
@endsection