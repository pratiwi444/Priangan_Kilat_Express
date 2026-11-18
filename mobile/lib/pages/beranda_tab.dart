import 'package:flutter/material.dart';

import '../api.dart';
import '../ui.dart';

class BerandaTab extends StatefulWidget {
  final int versi;
  final bool aktif;
  final VoidCallback kePaket;
  final VoidCallback keCod;
  const BerandaTab({
    super.key,
    required this.versi,
    required this.aktif,
    required this.kePaket,
    required this.keCod,
  });

  @override
  State<BerandaTab> createState() => _BerandaTabState();
}

class _BerandaTabState extends State<BerandaTab> {
  TugasData? _tugas;
  CodData? _cod;
  String? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    if (widget.aktif) _muat();
  }

  @override
  void didUpdateWidget(BerandaTab old) {
    super.didUpdateWidget(old);
    if (widget.aktif && (old.versi != widget.versi || !old.aktif)) _muat(diam: true);
  }

  Future<void> _muat({bool diam = false}) async {
    if (!diam && !_loading) setState(() => _loading = true);
    try {
      final t = await Api.paketTugas();
      final c = await Api.cod();
      if (!mounted) return;
      setState(() {
        _tugas = t;
        _cod = c;
        _error = null;
        _loading = false;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      if (e.status == 401 || e.wajibGanti) {
        tanganiError(context, e);
        return;
      }
      setState(() {
        _error = e.message;
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final t = _tugas;
    final c = _cod;

    if (_loading && t == null) return const Center(child: CircularProgressIndicator());

    final belumDisetor = c == null ? 0 : c.item.where((e) => !e.disetor).length;
    final perluDitagih = t == null ? 0 : t.paket.where((p) => p.isCod).fold<int>(0, (a, p) => a + p.nilaiCod);
    final jumlahCodDibawa = t == null ? 0 : t.paket.where((p) => p.isCod).length;

    return RefreshIndicator(
      onRefresh: () => _muat(diam: true),
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(12),
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(4, 4, 4, 12),
            child: Text(
              Api.nama.isEmpty ? 'Halo!' : 'Halo, ${Api.nama}',
              style: tema.textTheme.headlineSmall,
            ),
          ),
          if (_error != null && t == null) ...[
            const SizedBox(height: 60),
            Icon(Icons.cloud_off, size: 48, color: tema.colorScheme.error),
            const SizedBox(height: 12),
            Text(_error!, textAlign: TextAlign.center),
            const SizedBox(height: 12),
            Center(child: FilledButton(onPressed: () => _muat(), child: const Text('Coba lagi'))),
          ],
          if (t != null && c != null) ...[
            Card(
              color: tema.colorScheme.primaryContainer,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Paket yang sedang diantar'),
                    const SizedBox(height: 4),
                    Text('${t.jumlah} dari ${t.batas}', style: tema.textTheme.headlineMedium),
                    const SizedBox(height: 12),
                    LinearProgressIndicator(
                      value: t.batas == 0 ? 0 : (t.jumlah / t.batas).clamp(0.0, 1.0),
                      minHeight: 8,
                      borderRadius: BorderRadius.circular(4),
                    ),
                    const SizedBox(height: 12),
                    FilledButton.tonal(onPressed: widget.kePaket, child: const Text('Lihat paket')),
                  ],
                ),
              ),
            ),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(children: [
                      const Icon(Icons.payments_outlined),
                      const SizedBox(width: 8),
                      Text('COD', style: tema.textTheme.titleMedium),
                    ]),
                    const SizedBox(height: 12),
                    _baris('Perlu ditagih ($jumlahCodDibawa paket)', rupiah(perluDitagih)),
                    _baris('Belum disetor ($belumDisetor paket)', rupiah(c.totalBelumDisetor)),
                    const SizedBox(height: 8),
                    Align(
                      alignment: Alignment.centerRight,
                      child: TextButton(onPressed: widget.keCod, child: const Text('Lihat rincian COD')),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _baris(String label, String nilai) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 4),
        child: Row(
          children: [
            Expanded(child: Text(label)),
            Text(nilai, style: const TextStyle(fontWeight: FontWeight.w600)),
          ],
        ),
      );
}