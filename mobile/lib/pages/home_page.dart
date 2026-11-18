import 'package:flutter/material.dart';

import '../api.dart';
import '../ui.dart';
import 'detail_page.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  int _tab = 0;
  int _versi = 0; // dinaikkan untuk memuat ulang kedua tab

  void _muatUlang() => setState(() => _versi++);

  Future<void> _keluar() async {
    final ya = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Keluar'),
        content: const Text('Keluar dari akun kurir?'),
        actions: [
          TextButton(onPressed: () => Navigator.of(ctx).pop(false), child: const Text('Batal')),
          FilledButton(onPressed: () => Navigator.of(ctx).pop(true), child: const Text('Keluar')),
        ],
      ),
    );
    if (ya != true) return;
    await Api.logout();
    if (!mounted) return;
    Navigator.of(context).pushNamedAndRemoveUntil('/login', (r) => false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Kurir PKE'),
            if (Api.nama.isNotEmpty) Text(Api.nama, style: Theme.of(context).textTheme.bodySmall),
          ],
        ),
        actions: [IconButton(tooltip: 'Keluar', icon: const Icon(Icons.logout), onPressed: _keluar)],
      ),
      body: IndexedStack(
        index: _tab,
        children: [
          TugasTab(versi: _versi, onBerubah: _muatUlang),
          CodTab(versi: _versi),
        ],
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _tab,
        onDestinationSelected: (i) => setState(() {
          _tab = i;
          _versi++;
        }),
        destinations: const [
          NavigationDestination(icon: Icon(Icons.inventory_2_outlined), selectedIcon: Icon(Icons.inventory_2), label: 'Paket'),
          NavigationDestination(icon: Icon(Icons.payments_outlined), selectedIcon: Icon(Icons.payments), label: 'COD'),
        ],
      ),
    );
  }
}

// ======================================================================= Paket tugas

class TugasTab extends StatefulWidget {
  final int versi;
  final VoidCallback onBerubah;
  const TugasTab({super.key, required this.versi, required this.onBerubah});

  @override
  State<TugasTab> createState() => _TugasTabState();
}

class _TugasTabState extends State<TugasTab> {
  TugasData? _data;
  String? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _muat();
  }

  @override
  void didUpdateWidget(TugasTab old) {
    super.didUpdateWidget(old);
    if (old.versi != widget.versi) _muat(diam: true);
  }

  Future<void> _muat({bool diam = false}) async {
    if (!diam && !_loading) setState(() => _loading = true);
    try {
      final d = await Api.paketTugas();
      if (!mounted) return;
      setState(() {
        _data = d;
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

  Future<void> _buka(Paket p) async {
    final berhasil = await Navigator.of(context).push<bool>(
      MaterialPageRoute<bool>(builder: (_) => DetailPage(paket: p)),
    );
    if (berhasil == true) widget.onBerubah();
  }

  @override
  Widget build(BuildContext context) {
    if (_loading && _data == null) return const Center(child: CircularProgressIndicator());

    final tema = Theme.of(context);
    final d = _data;

    return RefreshIndicator(
      onRefresh: () => _muat(diam: true),
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(12),
        children: [
          if (_error != null && d == null) ...[
            const SizedBox(height: 80),
            Icon(Icons.cloud_off, size: 48, color: tema.colorScheme.error),
            const SizedBox(height: 12),
            Text(_error!, textAlign: TextAlign.center),
            const SizedBox(height: 12),
            Center(child: FilledButton(onPressed: () => _muat(), child: const Text('Coba lagi'))),
          ],
          if (d != null) ...[
            Card(
              color: tema.colorScheme.primaryContainer,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    const Icon(Icons.local_shipping),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text('Paket dikirim: ${d.jumlah} dari ${d.batas}', style: tema.textTheme.titleMedium),
                    ),
                  ],
                ),
              ),
            ),
            if (d.paket.isEmpty)
              const Padding(
                padding: EdgeInsets.only(top: 48),
                child: Center(child: Text('Tidak ada paket yang perlu diantar.')),
              ),
            for (final p in d.paket)
              Card(
                child: ListTile(
                  onTap: () => _buka(p),
                  title: Text(p.noResi, style: const TextStyle(fontWeight: FontWeight.w600)),
                  subtitle: Padding(
                    padding: const EdgeInsets.only(top: 4),
                    child: Text('${p.namaPenerima}\n${p.alamatPenerima ?? '-'}, ${p.kotaTujuan}'),
                  ),
                  isThreeLine: true,
                  trailing: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      if (p.isCod)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: tema.colorScheme.tertiaryContainer,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text('COD ${rupiah(p.nilaiCod)}', style: tema.textTheme.labelSmall),
                        ),
                      const Icon(Icons.chevron_right),
                    ],
                  ),
                ),
              ),
          ],
        ],
      ),
    );
  }
}

// ======================================================================= COD

class CodTab extends StatefulWidget {
  final int versi;
  const CodTab({super.key, required this.versi});

  @override
  State<CodTab> createState() => _CodTabState();
}

class _CodTabState extends State<CodTab> {
  CodData? _data;
  String? _error;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _muat();
  }

  @override
  void didUpdateWidget(CodTab old) {
    super.didUpdateWidget(old);
    if (old.versi != widget.versi) _muat(diam: true);
  }

  Future<void> _muat({bool diam = false}) async {
    if (!diam && !_loading) setState(() => _loading = true);
    try {
      final d = await Api.cod();
      if (!mounted) return;
      setState(() {
        _data = d;
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
    if (_loading && _data == null) return const Center(child: CircularProgressIndicator());

    final tema = Theme.of(context);
    final d = _data;

    return RefreshIndicator(
      onRefresh: () => _muat(diam: true),
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(12),
        children: [
          if (_error != null && d == null) ...[
            const SizedBox(height: 80),
            Icon(Icons.cloud_off, size: 48, color: tema.colorScheme.error),
            const SizedBox(height: 12),
            Text(_error!, textAlign: TextAlign.center),
            const SizedBox(height: 12),
            Center(child: FilledButton(onPressed: () => _muat(), child: const Text('Coba lagi'))),
          ],
          if (d != null) ...[
            Card(
              color: tema.colorScheme.primaryContainer,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Total COD belum disetor'),
                    const SizedBox(height: 4),
                    Text(rupiah(d.totalBelumDisetor), style: tema.textTheme.headlineSmall),
                  ],
                ),
              ),
            ),
            if (d.item.isEmpty)
              const Padding(
                padding: EdgeInsets.only(top: 48),
                child: Center(child: Text('Belum ada paket COD yang terkirim.')),
              ),
            for (final c in d.item)
              Card(
                child: ListTile(
                  title: Text(c.noResi),
                  subtitle: Text(rupiah(c.nilaiCod)),
                  trailing: Chip(
                    label: Text(c.disetor ? 'Sudah disetor' : 'Belum disetor'),
                    backgroundColor: c.disetor ? tema.colorScheme.secondaryContainer : tema.colorScheme.errorContainer,
                  ),
                ),
              ),
          ],
        ],
      ),
    );
  }
}