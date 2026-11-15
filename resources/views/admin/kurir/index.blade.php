@extends('layouts.app')
@section('judul', 'Kurir')
@section('isi')
@php $admin = auth()->user()->role === 'admin'; @endphp
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="m-0">Kurir</h4>
  @if ($admin) <a href="{{ route('admin.kurir.create') }}" class="btn btn-dark">Tambah</a> @endif
</div>
<table class="table table-sm table-bordered bg-white align-middle">
  <thead><tr><th>Kode</th><th>Nama</th><th>Telepon</th><th>Status</th>@if ($admin)<th>Aksi</th>@endif</tr></thead>
  <tbody>
  @forelse ($data as $k)
    <tr>
      <td>{{ $k->kode_kurir }}</td>
      <td>{{ $k->nama }}</td>
      <td>{{ $k->telepon }}</td>
      <td>{!! $k->aktif ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' !!}</td>
      @if ($admin)
      <td>
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-sm btn-outline-dark" href="{{ route('admin.kurir.edit', $k) }}">Ubah</a>

          <form method="post" action="{{ route('admin.kurir.reset', $k) }}" class="d-inline-flex gap-1 m-0">
            @csrf
            <input name="password" class="form-control form-control-sm" style="width:170px" placeholder="Password sementara">
            <button class="btn btn-sm btn-outline-warning">Reset</button>
          </form>

          @if ($k->aktif)
          <form method="post" action="{{ route('admin.kurir.nonaktif', $k) }}" class="m-0"
                onsubmit="return confirm('Nonaktifkan kurir {{ $k->kode_kurir }}?')">
            @csrf
            <button class="btn btn-sm btn-outline-danger">Nonaktifkan</button>
          </form>
          @endif
        </div>
      </td>
      @endif
    </tr>
  @empty
    <tr><td colspan="5" class="text-center text-muted">Belum ada data.</td></tr>
  @endforelse
  </tbody>
</table>
@endsection