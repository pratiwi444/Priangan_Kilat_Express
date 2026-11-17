
@extends('layouts.app')

@section('judul', 'Penugasan Kurir')

@section('isi')

<div class="container-fluid">

    <h4 class="mb-3">Penugasan Kurir</h4>

    {{-- NOTIFIKASI BERHASIL --}}
    @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- NOTIFIKASI ERROR --}}
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- FILTER ZONA --}}
    <form method="GET"
          action="{{ route('admin.penugasan') }}"
          class="mb-3">

        <div class="d-flex align-items-center gap-2">

            <label class="fw-semibold">Zona</label>

            <select name="zona_id"
                    class="form-select"
                    style="width:220px"
                    onchange="this.form.submit()">

                <option value="">Semua zona</option>

                @foreach($zonas as $z)
                    <option value="{{ $z->id }}"
                        @selected($zonaId == $z->id)>
                        {{ $z->nama }}
                    </option>
                @endforeach

            </select>

        </div>
    </form>

    {{-- FORM PENUGASAN --}}
    <form method="POST"
          action="{{ route('admin.penugasan.store') }}">

        @csrf

        <div class="row g-3">

            {{-- TABEL PAKET --}}
            <div class="col-lg-8">

                <div class="card shadow-sm">

                    <div class="card-header fw-bold">
                        Paket yang Perlu Ditugaskan
                    </div>

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="45">
                                        <input type="checkbox"
                                               id="pilihSemua">
                                    </th>

                                    <th>No. Resi</th>
                                    <th>Nama Penerima</th>
                                    <th>Alamat</th>
                                    <th>No. Telepon</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($pakets as $p)

                                    <tr>

                                        <td class="text-center">
                                            <input type="checkbox"
                                                   class="paket-checkbox"
                                                   name="paket_ids[]"
                                                   value="{{ $p->id }}">
                                        </td>

                                        <td class="fw-semibold">
                                            {{ $p->no_resi }}
                                        </td>

                                        <td>
                                            {{ $p->nama_penerima }}
                                        </td>

                                        <td>
                                            {{ $p->alamat_penerima }}
                                        </td>

                                        <td>
                                            {{ $p->telepon_penerima }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="5"
                                            class="text-center text-muted py-4">
                                            Tidak ada paket siap ditugaskan.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            {{-- PILIH KURIR --}}
            <div class="col-lg-4">

                <div class="card shadow-sm">

                    <div class="card-header fw-bold">
                        Pilih Kurir
                    </div>

                    <div class="card-body">

                        @forelse($kurirs as $k)

                            <label class="d-flex align-items-start gap-2 border rounded p-3 mb-2"
                                   style="cursor:pointer">

                                <input type="radio"
                                       name="kurir_id"
                                       value="{{ $k->id }}"
                                       class="form-check-input mt-1"
                                       required
                                       @disabled($k->jumlah_dikirim >= 20)>

                                <div class="flex-grow-1">

                                    <div class="fw-bold">
                                        {{ $k->kode_kurir }}
                                        - {{ $k->nama }}
                                    </div>

                                    <div class="small text-muted">
                                        {{ $k->jumlah_dikirim }} / 20 paket Dikirim
                                    </div>

                                    {{-- PROGRESS KAPASITAS --}}
                                    <div class="progress mt-2"
                                         style="height:8px">

                                        <div class="progress-bar
                                            {{ $k->jumlah_dikirim >= 20 ? 'bg-danger' : 'bg-success' }}"
                                             style="width:{{ min(100, $k->jumlah_dikirim / 20 * 100) }}%">
                                        </div>

                                    </div>

                                    @if($k->jumlah_dikirim >= 20)
                                        <small class="text-danger">
                                            Kapasitas penuh
                                        </small>
                                    @else
                                        <small class="text-success">
                                            Tersedia {{ 20 - $k->jumlah_dikirim }} slot
                                        </small>
                                    @endif

                                </div>

                            </label>

                        @empty

                            <div class="alert alert-warning mb-0">
                                Belum ada kurir aktif.
                            </div>

                        @endforelse

                        {{-- TOMBOL TUGASKAN --}}
                        <button type="submit"
                                class="btn btn-success w-100 mt-3"
                                @disabled($pakets->isEmpty() || $kurirs->where('jumlah_dikirim', '<', 20)->isEmpty())>

                            <i class="bi bi-truck"></i>
                            Tugaskan Paket

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </form>

</div>

{{-- JAVASCRIPT PILIH SEMUA PAKET --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const pilihSemua = document.getElementById('pilihSemua');

    const paketCheckbox = document.querySelectorAll('.paket-checkbox');

    if (pilihSemua) {

        pilihSemua.addEventListener('change', function () {

            paketCheckbox.forEach(function (checkbox) {
                checkbox.checked = pilihSemua.checked;
            });

        });

    }

    paketCheckbox.forEach(function (checkbox) {

        checkbox.addEventListener('change', function () {

            const total = paketCheckbox.length;

            const terpilih = document.querySelectorAll(
                '.paket-checkbox:checked'
            ).length;

            if (pilihSemua) {
                pilihSemua.checked = total > 0 && total === terpilih;
            }

        });

    });

});
</script>

@endsection
