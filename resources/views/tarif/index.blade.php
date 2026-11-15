@extends('layouts.app')
@section('judul', 'Tarif zona')
@section('isi')
@php $manajer = auth()->user()->role === 'manajer'; @endphp
<h4 class="mb-3">Tarif per kg</h4>
@if ($manajer)
  <p class="text-muted">Tarif baru hanya berlaku untuk paket yang dibuat sesudahnya.</p>
@endif
<table class="table table-sm table-bordered bg-white align-middle" style="max-width:720px">
  <thead>
    <tr><th>Zona</th><th>Tarif per kg</th>@if ($manajer)<th style="width:260px">Ubah</th>@endif</tr>
  </thead>
  <tbody>
  @foreach ($zona as $z)
    <tr>
      <td>{{ $z->nama }}</td>
      <td>Rp{{ number_format($z->tarif_per_kg, 0, ',', '.') }}</td>
      @if ($manajer)
      <td>
        <form method="post" action="{{ route('manajer.tarif.update', $z) }}" class="d-flex gap-1 m-0">
          @csrf
          <input name="tarif_per_kg" type="number" min="1" class="form-control form-control-sm"
                 value="{{ old('tarif_per_kg', $z->tarif_per_kg) }}">
          <button class="btn btn-sm btn-dark">Simpan</button>
        </form>
      </td>
      @endif
    </tr>
  @endforeach
  </tbody>
</table>
@endsection