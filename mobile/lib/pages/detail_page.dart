import 'package:flutter/material.dart';

import '../api.dart';
import '../ui.dart';

class DetailPage extends StatefulWidget {
  final Paket paket;
  const DetailPage({super.key, required this.paket});

  @override
  State<DetailPage> createState() => _DetailPageState();
}

class _DetailPageState extends State<DetailPage> {
  bool _proses = false;

  Paket get p => widget.paket;

  Future<void> _terkirim() async {
    final ya = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Tandai Terkirim'),
        content: Text(
          p.isCod
              ? 'Pastikan Anda sudah menerima uang COD ${rupiah(p.nilaiCod)} dari penerima.'
              : 'Tandai paket ${p.noResi} sebagai terkirim?',
        ),
        actions: [
          TextButton(onPressed: () => Navigator.of(ctx).pop(false), child: const Text('Batal')),
          FilledButton(onPressed: () => Navigator.of(ctx).pop(true), child: const Text('Ya, terkirim')),
        ],
      ),
    );
    if (ya != true) return;
    await _jalankan(() => Api.terkirim(p.id), 'Paket ${p.noResi} ditandai Terkirim.');
  }

  Future<void> _gagal() async {
    final catatan = await showDialog<String>(
      context: context,
      builder: (ctx) => const _CatatanDialog(),
    );
    if (catatan == null) return;
    await _jalankan(() => Api.gagalKirim(p.id, catatan), 'Paket ${p.noResi} dicatat Gagal Kirim.');
  }

  Future<void> _jalankan(Future<void> Function() aksi, String pesanSukses) async {
    setState(() => _proses = true);
    try {
      await aksi();
      if (!mounted) return;
      final messenger = ScaffoldMessenger.of(context);
      Navigator.of(context).pop(true);
      messenger.showSnackBar(SnackBar(content: Text(pesanSukses)));
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() => _proses = false);
      tanganiError(context, e);
    }
  }

  Widget _baris(String label, String nilai) => Padding(
        padding: const EdgeInsets.symmetric(vertical: 6),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            SizedBox(width: 110, child: Text(label, style: const TextStyle(color: Colors.grey))),
            Expanded(child: Text(nilai)),
          ],
        ),
      );

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Scaffold(
      appBar: AppBar(title: Text(p.noResi)),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  _baris('Penerima', p.namaPenerima),
                  _baris('Telepon', p.teleponPenerima),
                  _baris('Alamat', p.alamatPenerima ?? '-'),
                  _baris('Kota tujuan', p.kotaTujuan),
                  _baris('Berat', '${p.berat} kg'),
                  _baris('Layanan', p.layanan.isEmpty ? '-' : '${p.layanan[0].toUpperCase()}${p.layanan.substring(1)}'),
                ],
              ),
            ),
          ),
          if (p.isCod)
            Card(
              color: tema.colorScheme.tertiaryContainer,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    const Icon(Icons.payments),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text('Tagih ke penerima: ${rupiah(p.nilaiCod)}', style: tema.textTheme.titleMedium),
                    ),
                  ],
                ),
              ),
            ),
          const SizedBox(height: 16),
          FilledButton.icon(
            onPressed: _proses ? null : _terkirim,
            icon: const Icon(Icons.check_circle),
            label: const Text('Terkirim'),
          ),
          const SizedBox(height: 8),
          OutlinedButton.icon(
            onPressed: _proses ? null : _gagal,
            icon: const Icon(Icons.cancel_outlined),
            label: const Text('Gagal Kirim'),
          ),
          if (_proses) const Padding(padding: EdgeInsets.only(top: 16), child: LinearProgressIndicator()),
        ],
      ),
    );
  }
}

class _CatatanDialog extends StatefulWidget {
  const _CatatanDialog();

  @override
  State<_CatatanDialog> createState() => _CatatanDialogState();
}

class _CatatanDialogState extends State<_CatatanDialog> {
  final _c = TextEditingController();
  String? _error;

  @override
  void dispose() {
    _c.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Gagal Kirim'),
      content: TextField(
        controller: _c,
        autofocus: true,
        maxLines: 3,
        decoration: InputDecoration(
          labelText: 'Catatan (wajib)',
          hintText: 'contoh: penerima tidak di tempat',
          errorText: _error,
          border: const OutlineInputBorder(),
        ),
      ),
      actions: [
        TextButton(onPressed: () => Navigator.of(context).pop(), child: const Text('Batal')),
        FilledButton(
          onPressed: () {
            final t = _c.text.trim();
            if (t.isEmpty) {
              setState(() => _error = 'Catatan wajib diisi.');
              return;
            }
            Navigator.of(context).pop(t);
          },
          child: const Text('Simpan'),
        ),
      ],
    );
  }
}