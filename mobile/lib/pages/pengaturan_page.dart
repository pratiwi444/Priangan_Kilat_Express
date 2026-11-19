import 'package:flutter/material.dart';

import '../api.dart';
import '../ui.dart';

class PengaturanPage extends StatefulWidget {
  const PengaturanPage({super.key});

  @override
  State<PengaturanPage> createState() => _PengaturanPageState();
}

class _PengaturanPageState extends State<PengaturanPage> {
  late final TextEditingController _base = TextEditingController(text: Api.base);
  bool _menguji = false;

  @override
  void dispose() {
    _base.dispose();
    super.dispose();
  }

  bool _valid(String v) {
    final u = Uri.tryParse(Api.rapikanBase(v));
    return u != null && u.hasAuthority && u.host.isNotEmpty;
  }

  Future<void> _simpan({bool tutup = true}) async {
    if (!_valid(_base.text)) {
      tampilPesan(context, 'Alamat server tidak valid. Contoh: http://192.168.1.10:8000/api');
      return;
    }
    await Api.simpanBase(_base.text);
    if (!mounted) return;
    _base.text = Api.base;
    if (tutup) {
      Navigator.of(context).pop();
      tampilPesan(context, 'Alamat server disimpan.');
    }
  }

  Future<void> _tes() async {
    if (!_valid(_base.text)) {
      tampilPesan(context, 'Alamat server tidak valid. Contoh: http://192.168.1.10:8000/api');
      return;
    }
    await _simpan(tutup: false);
    if (!mounted) return;
    setState(() => _menguji = true);
    try {
      await Api.tesKoneksi();
      if (!mounted) return;
      tampilPesan(context, 'Server terjangkau.');
    } on ApiException catch (e) {
      if (!mounted) return;
      tampilPesan(context, e.message);
    } finally {
      if (mounted) setState(() => _menguji = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Pengaturan server')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          TextField(
            controller: _base,
            keyboardType: TextInputType.url,
            autocorrect: false,
            decoration: const InputDecoration(
              labelText: 'Alamat API',
              helperText: 'Cukup alamat server, akhiran /api ditambahkan otomatis.\nEmulator: http://10.0.2.2:8000\nHP: http://IP-laptop:8000 (satu Wi-Fi)',
              helperMaxLines: 3,
              border: OutlineInputBorder(),
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: _menguji ? null : _tes,
                  child: _menguji
                      ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                      : const Text('Tes koneksi'),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(child: FilledButton(onPressed: _simpan, child: const Text('Simpan'))),
            ],
          ),
        ],
      ),
    );
  }
}