@extends('layouts.app')
@section('judul', 'Pelanggan')
@section('isi')
@php $admin = auth()->user()->role === 'admin'; @endphp
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="m-0">Pelanggan</h4>
  @if ($admin) <a href="{{ route('admin.pelanggan.create') }}" class="btn btn-dark">Tambah</a> @endif
</div>
<table class="table table-sm table-bordered bg-white">
  <thead><tr><th>Nama</th><th>Telepon</th>@if ($admin)<th style="width:90px"></th>@endif</tr></thead>
  <tbody>
  @forelse ($data as $p)
    <tr>
      <td>{{ $p->nama }}</td>
      <td>{{ $p->telepon }}</td>
      @if ($admin)
        <td><a class="btn btn-sm btn-outline-dark" href="{{ route('admin.pelanggan.edit', $p) }}">Ubah</a></td>
      @endif
    </tr>
  @empty
    <tr><td colspan="3" class="text-center text-muted">Belum ada data.</td></tr>
  @endforelse
  </tbody>
</table>
@endsection