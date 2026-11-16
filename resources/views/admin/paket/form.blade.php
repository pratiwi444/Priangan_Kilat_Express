@extends('layouts.app')
@section('judul', 'Tambah paket')
@section('isi')
<div class="card" style="max-width:640px"><div class="card-body">
  <h5 class="mb-3">Tambah paket</h5>
  <form method="post" action="{{ route('admin.paket.store') }}">
    @csrf

    <label class="form-label">Pengirim</label>
    <select name="pelanggan_id" class="form-select mb-2">
      <option value="">Pilih pelanggan</option>
      @foreach ($pelanggan as $p)
        <option value="{{ $p->id }}" @selected(old('pelanggan_id') == $p->id)>{{ $p->nama }} ({{ $p->telepon }})</option>
      @endforeach
    </select>

    <div class="row g-2 mb-2">
      <div class="col-md-6">
        <label class="form-label">Nama penerima</label>
        <input name="nama_penerima" class="form-control" value="{{ old('nama_penerima') }}">
      </div>
      <div class="col-md-6">
        <label class="form-label">Telepon penerima</label>
        <input name="telepon_penerima" class="form-control" value="{{ old('telepon_penerima') }}">
      </div>
    </div>

    <label class="form-label">Alamat penerima</label>
    <input name="alamat_penerima" class="form-control mb-2" value="{{ old('alamat_penerima') }}">

    <label class="form-label">Kota tujuan</label>
    <input name="kota_tujuan" class="form-control mb-2" value="{{ old('kota_tujuan') }}">

    <div class="row g-2 mb-2">
      <div class="col-md-6">
        <label class="form-label">Berat (kg)</label>
        <input name="berat" type="number" step="0.01" min="0" class="form-control" value="{{ old('berat') }}">
      </div>
      <div class="col-md-6">
        <label class="form-label">Layanan</label>
        <select name="layanan" class="form-select">
          <option value="reguler" @selected(old('layanan', 'reguler') === 'reguler')>Reguler</option>
          <option value="express" @selected(old('layanan') === 'express')>Express</option>
        </select>
      </div>
    </div>

    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" name="is_cod" value="1" id="is_cod" @checked(old('is_cod'))>
      <label class="form-check-label" for="is_cod">Paket COD</label>
    </div>
    <label class="form-label">Nilai COD (Rp)</label>
    <input name="nilai_cod" type="number" min="1" class="form-control mb-2" value="{{ old('nilai_cod') }}">

    @php $tampilManual = $errors->has('zona_id_manual') || old('zona_id_manual'); @endphp
<div id="zonaManualBox" class="{{ $tampilManual ? '' : 'd-none' }} mb-2">
  <label class="form-label">Zona manual</label>
  <select name="zona_id_manual" id="zonaManual" class="form-select" @disabled(!$tampilManual)>
    <option value="">Pilih zona</option>
    @foreach ($zona as $z)
      <option value="{{ $z->id }}" @selected(old('zona_id_manual') == $z->id)>{{ $z->nama }}</option>
    @endforeach
  </select>
  <div class="form-text">Zona kota ini tidak ditemukan otomatis. Pilih manual, lalu klik Cek lagi.</div>
</div>

<button type="button" id="cekZona" class="btn btn-outline-dark mb-2">Cek zona &amp; biaya</button>
<div id="hasilPratinjau" class="mb-3"></div>

    <button class="btn btn-dark">Simpan</button>
    <a href="{{ route('admin.paket.index') }}" class="btn btn-link">Batal</a>
  </form>
</div></div>
<script>
(function () {
  const f = document.querySelector('form[action="{{ route('admin.paket.store') }}"]');
  const tombol = document.getElementById('cekZona');
  const hasil = document.getElementById('hasilPratinjau');
  const boxManual = document.getElementById('zonaManualBox');
  const selManual = document.getElementById('zonaManual');
  const rp = n => 'Rp' + Number(n).toLocaleString('id-ID');

  ['kota_tujuan', 'berat', 'layanan', 'nilai_cod', 'is_cod'].forEach(n =>
    f[n].addEventListener('input', () => { hasil.innerHTML = ''; }));

  tombol.addEventListener('click', async () => {
    const data = {
      kota_tujuan: f.kota_tujuan.value,
      berat: f.berat.value,
      layanan: f.layanan.value,
      zona_id_manual: selManual.disabled ? '' : selManual.value,
    };
    if (f.is_cod.checked && f.nilai_cod.value) data.nilai_cod = f.nilai_cod.value;

    hasil.textContent = 'Memeriksa...';
    try {
      const res = await fetch('{{ route('admin.paket.cek-zona') }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify(data),
      });
      const j = await res.json();

      if (res.status === 422) {
        hasil.innerHTML = '';
        const e = document.createElement('div');
        e.className = 'text-danger';
        e.textContent = Object.values(j.errors).flat().join(' ');
        hasil.appendChild(e);
        return;
      }
      if (!j.zona_ditemukan) {
        boxManual.classList.remove('d-none');
        selManual.disabled = false;
        hasil.innerHTML = '<div class="text-danger">Zona tidak ditemukan otomatis. Pilih zona manual di atas, lalu klik Cek lagi.</div>';
        return;
      }
      hasil.innerHTML = `
        <div class="card card-body py-2">
          <div>${j.zona}${j.manual ? ' <span class="badge bg-warning text-dark">manual</span>' : ''} &middot; ${rp(j.tarif_per_kg)}/kg</div>
          <table class="table table-sm mb-0 mt-1">
            <tr><td>Ongkir</td><td class="text-end">${rp(j.ongkir)}</td></tr>
            <tr><td>Biaya COD</td><td class="text-end">${rp(j.biaya_cod)}</td></tr>
            <tr class="fw-bold"><td>Total dibayar pengirim</td><td class="text-end">${rp(j.total)}</td></tr>
          </table>
        </div>`;
    } catch (err) {
      hasil.innerHTML = '<div class="text-danger">Gagal menghubungi server. Coba lagi.</div>';
    }
  });
})();
</script>
@endsection