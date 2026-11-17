@extends('layouts.app')
@section('judul', 'Impor CSV')
@section('isi')
<h4 class="mb-3">Impor Data CSV</h4>

<div class="card mb-4" style="max-width:720px"><div class="card-body">
  <form method="post" action="{{ route('admin.impor.store') }}" enctype="multipart/form-data">
    @csrf
    <label class="form-label fw-bold">Jenis data</label>
    <select name="jenis" class="form-select mb-3">
      <option value="">Pilih jenis data</option>
      <option value="kurir" @selected(old('jenis') === 'kurir')>Kurir</option>
      <option value="pelanggan" @selected(old('jenis') === 'pelanggan')>Pelanggan</option>
      <option value="paket" @selected(old('jenis') === 'paket')>Paket historis</option>
    </select>

    <label class="form-label fw-bold">Berkas CSV</label>
    <input type="file" name="berkas" accept=".csv,text/csv" class="form-control mb-1">
    <div class="form-text mb-3">Urutan yang disarankan: Kurir, Pelanggan, lalu Paket historis. Data yang sudah ada dilewati, tidak ditimpa.</div>

    <button class="btn btn-dark">Impor</button>
  </form>
</div></div>

@if (session('hasil'))
  @php $h = session('hasil'); @endphp
  <div class="card" style="max-width:720px"><div class="card-body">
    <h6 class="mb-1">Hasil Impor</h6>
    <div class="text-muted small mb-2">{{ $h['berkas'] }}</div>
    <table class="table table-sm mb-0">
      <tbody>
      @foreach ($h['baris'] as [$keterangan, $jumlah])
        <tr><td>{{ $keterangan }}</td><td class="text-end fw-bold">{{ $jumlah }}</td></tr>
      @endforeach
      </tbody>
    </table>

    @if (count($h['tidak_valid']))
      <div class="mt-3 fw-bold">Baris tidak valid (dilewati)</div>
      <ul class="small mb-0">
        @foreach ($h['tidak_valid'] as $pesan)<li>{{ $pesan }}</li>@endforeach
      </ul>
    @endif
  </div></div>
@endif
@endsection
