
@extends('layouts.app')

@section('judul', 'Impor CSV')

@section('isi')
<h4 class="mb-3">Impor Data CSV</h4>

<div class="card mb-4" style="max-width:720px">
    <div class="card-body">
        <form method="post"
              action="{{ route('admin.impor.store') }}"
              enctype="multipart/form-data">

            @csrf

            <label class="form-label fw-bold">Jenis data</label>
            <select name="jenis" class="form-select mb-3" required>
                <option value="">Pilih jenis data</option>
                <option value="kurir" @selected(old('jenis') === 'kurir')>
                    Kurir
                </option>
                <option value="pelanggan" @selected(old('jenis') === 'pelanggan')>
                    Pelanggan
                </option>
                <option value="paket" @selected(old('jenis') === 'paket')>
                    Paket historis
                </option>
            </select>

            <label class="form-label fw-bold">Berkas CSV</label>
            <input type="file"
                   name="berkas"
                   accept=".csv,text/csv"
                   class="form-control mb-1"
                   required>

            <div class="form-text mb-3">
                Urutan yang disarankan: Kurir, Pelanggan, lalu Paket historis.
                Data yang sudah ada dilewati, tidak ditimpa.
            </div>

            <button type="submit" class="btn btn-dark">
                Impor
            </button>
        </form>
    </div>
</div>

@if (session('hasil'))
    @php $h = session('hasil'); @endphp

    <div class="card" style="max-width:720px">
        <div class="card-body">

            <h6 class="mb-1">Hasil Impor</h6>

            <div class="text-muted small mb-3">
                {{ $h['berkas'] }}
            </div>

            <table class="table table-sm mb-0">
                <tbody>
                    @foreach ($h['baris'] as [$keterangan, $jumlah])
                        <tr>
                            <td>{{ $keterangan }}</td>
                            <td class="text-end fw-bold">
                                {{ $jumlah }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- PEMERIKSAAN RESI GANDA --}}
            @if (!empty($h['resi_ganda']))
                <div class="alert alert-warning mt-4 mb-3">
                    <div class="fw-bold mb-1">
                        ⚠ Perlu Verifikasi: Resi Ganda
                    </div>

                    <div class="small">
                        Ditemukan nomor resi yang tercatat lebih dari
                        satu kali. Data pertama tetap diimpor,
                        sedangkan baris duplikat dilewati.
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>No. Resi</th>
                                <th>Baris Pertama</th>
                                <th>Baris Duplikat</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($h['resi_ganda'] as $duplikat)
                                <tr>
                                    <td class="fw-bold">
                                        {{ $duplikat['no_resi'] }}
                                    </td>

                                    <td>
                                        {{ $duplikat['baris_pertama'] }}
                                    </td>

                                    <td>
                                        {{ $duplikat['baris_duplikat'] }}
                                    </td>

                                    <td>
                                        <span class="badge bg-warning text-dark">
                                            Perlu Verifikasi
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif (($h['baris'] ?? []) && ($h['resi_ganda'] ?? []) === [])
                @if (old('jenis') === 'paket')
                    <div class="alert alert-success mt-3">
                        Tidak ditemukan resi ganda.
                    </div>
                @endif
            @endif

            {{-- BARIS TIDAK VALID --}}
            @if (!empty($h['tidak_valid']))
                <div class="mt-4">
                    <div class="fw-bold text-danger mb-2">
                        Baris Tidak Valid (Dilewati)
                    </div>

                    <ul class="small mb-0">
                        @foreach ($h['tidak_valid'] as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

        </div>
    </div>
@endif
@endsection
