@extends('layouts.app')
@section('judul', 'Laporan')
@section('isi')
<h4 class="mb-3">Laporan</h4>

<form method="get" action="{{ route('manajer.laporan') }}" class="row g-2 align-items-end mb-4">
  <div class="col-auto">
    <label class="form-label mb-0">Dari tanggal</label>
    <input type="date" name="dari" value="{{ $dari }}" class="form-control">
  </div>
  <div class="col-auto">
    <label class="form-label mb-0">Sampai tanggal</label>
    <input type="date" name="sampai" value="{{ $sampai }}" class="form-control">
  </div>
  <div class="col-auto"><button class="btn btn-dark">Tampilkan</button></div>
</form>

<div class="row g-3 mb-4">
  <div class="col-lg-4">
    <div class="card"><div class="card-body">
      <h6>Pengiriman per status</h6>
      <div class="grafik"><canvas id="gStatus"></canvas></div>
      <div id="kosongStatus" class="text-muted small d-none">Tidak ada paket pada periode ini.</div>
    </div></div>
  </div>
  <div class="col-lg-4">
    <div class="card"><div class="card-body">
      <h6>Pendapatan per zona</h6>
      <div class="grafik"><canvas id="gZona"></canvas></div>
      <div id="kosongZona" class="text-muted small d-none">Tidak ada pendapatan pada periode ini.</div>
    </div></div>
  </div>
  <div class="col-lg-4">
    <div class="card"><div class="card-body">
      <h6>Rekap COD per kurir</h6>
      <div class="grafik"><canvas id="gCod"></canvas></div>
      <div id="kosongCod" class="text-muted small d-none">Belum ada paket COD Terkirim.</div>
    </div></div>
  </div>
</div>

<h6>Pengiriman <span class="text-muted fw-normal">(menurut tanggal masuk, status saat ini)</span></h6>
<table class="table table-sm table-bordered bg-white" style="max-width:420px">
  <thead><tr><th>Status</th><th class="text-end">Jumlah paket</th></tr></thead>
  <tbody>
  @foreach ($pengiriman as $s)
    <tr><td>{{ $s['label'] }}</td><td class="text-end">{{ $s['jumlah'] }}</td></tr>
  @endforeach
    <tr class="fw-bold"><td>Total</td><td class="text-end">{{ $pengiriman->sum('jumlah') }}</td></tr>
  </tbody>
</table>

<h6 class="mt-4">Pendapatan per zona</h6>
<table class="table table-sm table-bordered bg-white" style="max-width:760px">
  <thead>
    <tr><th>Zona</th><th class="text-end">Paket</th><th class="text-end">Ongkir</th><th class="text-end">Biaya COD</th><th class="text-end">Asuransi</th><th class="text-end">Total</th></tr>
  </thead>
  <tbody>
  @forelse ($pendapatan as $p)
    <tr>
      <td>{{ $p->zona }}</td>
      <td class="text-end">{{ $p->jumlah_paket }}</td>
      <td class="text-end">Rp{{ number_format($p->ongkir, 0, ',', '.') }}</td>
      <td class="text-end">Rp{{ number_format($p->biaya_cod, 0, ',', '.') }}</td>
      <td class="text-end">Rp{{ number_format($p->biaya_asuransi, 0, ',', '.') }}</td>
      <td class="text-end">Rp{{ number_format($p->total, 0, ',', '.') }}</td>
    </tr>
  @empty
    <tr><td colspan="6" class="text-center text-muted">Tidak ada paket pada periode ini.</td></tr>
  @endforelse
  </tbody>
  @if ($pendapatan->count())
  <tfoot>
    <tr class="fw-bold">
      <td>Total</td>
      <td class="text-end">{{ $pendapatan->sum('jumlah_paket') }}</td>
      <td class="text-end">Rp{{ number_format($pendapatan->sum('ongkir'), 0, ',', '.') }}</td>
      <td class="text-end">Rp{{ number_format($pendapatan->sum('biaya_cod'), 0, ',', '.') }}</td>
      <td class="text-end">Rp{{ number_format($pendapatan->sum('biaya_asuransi'), 0, ',', '.') }}</td>
      <td class="text-end">Rp{{ number_format($pendapatan->sum('total'), 0, ',', '.') }}</td>
    </tr>
  </tfoot>
  @endif
</table>

<h6 class="mt-4">Rekap COD per kurir <span class="text-muted fw-normal">(paket COD Terkirim, semua periode)</span></h6>
<table class="table table-sm table-bordered bg-white" style="max-width:760px">
  <thead>
    <tr><th>Kurir</th><th class="text-end">Belum disetor</th><th class="text-end">Sudah disetor</th></tr>
  </thead>
  <tbody>
  @forelse ($rekapCod as $c)
    <tr>
      <td>{{ $c->kode_kurir }} - {{ $c->nama }}</td>
      <td class="text-end">Rp{{ number_format($c->belum_disetor, 0, ',', '.') }}</td>
      <td class="text-end">Rp{{ number_format($c->sudah_disetor, 0, ',', '.') }}</td>
    </tr>
  @empty
    <tr><td colspan="3" class="text-center text-muted">Belum ada paket COD Terkirim.</td></tr>
  @endforelse
  </tbody>
</table>

<h6 class="mt-4">Performa kurir <span class="text-muted fw-normal">(paket yang dipegang kurir, menurut tanggal masuk)</span></h6>
<table class="table table-sm table-bordered bg-white" style="max-width:760px">
  <thead>
    <tr><th>Kurir</th><th class="text-end">Terkirim</th><th class="text-end">Gagal</th><th class="text-end">% Terkirim</th><th class="text-end">% Gagal</th></tr>
  </thead>
  <tbody>
  @foreach ($performa as $k)
    @php $n = $k->terkirim + $k->gagal; @endphp
    <tr>
      <td>{{ $k->kode_kurir }} - {{ $k->nama }}</td>
      <td class="text-end">{{ $k->terkirim }}</td>
      <td class="text-end">{{ $k->gagal }}</td>
      <td class="text-end">{{ $n ? number_format($k->terkirim / $n * 100, 1, ',', '.') . '%' : '-' }}</td>
      <td class="text-end">{{ $n ? number_format($k->gagal / $n * 100, 1, ',', '.') . '%' : '-' }}</td>
    </tr>
  @endforeach
  </tbody>
</table>
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
<script>
(function () {
  if (typeof Chart === 'undefined') return;   // pustaka tidak termuat: tabel tetap tampil

  const rp = n => 'Rp' + Number(n).toLocaleString('id-ID');
  const kosong = (grafik, pesan) => {
    document.getElementById(grafik).classList.add('d-none');
    document.getElementById(pesan).classList.remove('d-none');
  };

  // 1. Pengiriman per status (urutan: Diterima, Transit, Dikirim, Terkirim, Gagal Kirim, Retur)
  const status = @json($pengiriman->values());
  if (status.reduce((a, s) => a + s.jumlah, 0) > 0) {
    new Chart(document.getElementById('gStatus'), {
      type: 'doughnut',
      data: {
        labels: status.map(s => s.label),
        datasets: [{
          data: status.map(s => s.jumlah),
          backgroundColor: ['#6B7A70', '#3F6B8F', '#2F6B47', '#1F4D32', '#9A6A12', '#9B3B3B'],
        }],
      },
      options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
    });
  } else { kosong('gStatus', 'kosongStatus'); }

  // 2. Pendapatan per zona (ongkir + biaya COD)
  const zona = @json($pendapatan->values());
  if (zona.length) {
    new Chart(document.getElementById('gZona'), {
      type: 'bar',
      data: {
        labels: zona.map(z => z.zona.replace(/ \(.*\)/, '')),
        datasets: [
          { label: 'Ongkir',    data: zona.map(z => Number(z.ongkir)),    backgroundColor: '#2F5D4A' },
          { label: 'Biaya COD', data: zona.map(z => Number(z.biaya_cod)), backgroundColor: '#9A6A12' },
        ],
      },
      options: {
        maintainAspectRatio: false,
        scales: { x: { stacked: true }, y: { stacked: true, ticks: { callback: v => rp(v) } } },
        plugins: {
          legend: { position: 'bottom' },
          tooltip: { callbacks: {
            title: items => zona[items[0].dataIndex].zona,
            label: c => c.dataset.label + ': ' + rp(c.parsed.y),
          } },
        },
      },
    });
  } else { kosong('gZona', 'kosongZona'); }

  // 3. Rekap COD per kurir (belum dan sudah disetor)
  const cod = @json($rekapCod->values());
  if (cod.length) {
    new Chart(document.getElementById('gCod'), {
      type: 'bar',
      data: {
        labels: cod.map(c => c.kode_kurir),
        datasets: [
          { label: 'Belum disetor', data: cod.map(c => Number(c.belum_disetor)), backgroundColor: '#9B3B3B' },
          { label: 'Sudah disetor', data: cod.map(c => Number(c.sudah_disetor)), backgroundColor: '#2F6B47' },
        ],
      },
      options: {
        maintainAspectRatio: false,
        scales: { x: { stacked: true }, y: { stacked: true, ticks: { callback: v => rp(v) } } },
        plugins: {
          legend: { position: 'bottom' },
          tooltip: { callbacks: { label: c => c.dataset.label + ': ' + rp(c.parsed.y) } },
        },
      },
    });
  } else { kosong('gCod', 'kosongCod'); }
})();
</script>
@endsection