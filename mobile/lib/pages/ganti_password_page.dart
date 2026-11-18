import 'package:flutter/material.dart';

import '../api.dart';
import '../ui.dart';

class GantiPasswordPage extends StatefulWidget {
  final bool wajib;
  const GantiPasswordPage({super.key, this.wajib = true});

  @override
  State<GantiPasswordPage> createState() => _GantiPasswordPageState();
}

class _GantiPasswordPageState extends State<GantiPasswordPage> {
  final _baru = TextEditingController();
  final _ulang = TextEditingController();
  bool _loading = false;
  bool _lihat = false;
  String? _error;

  @override
  void dispose() {
    _baru.dispose();
    _ulang.dispose();
    super.dispose();
  }

  Future<void> _simpan() async {
    if (_baru.text.length < 8) {
      setState(() => _error = 'Password baru minimal 8 karakter.');
      return;
    }
    if (_baru.text != _ulang.text) {
      setState(() => _error = 'Konfirmasi password tidak sama.');
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      await Api.gantiPassword(_baru.text);
      if (!mounted) return;
      final messenger = ScaffoldMessenger.of(context);
      if (widget.wajib) {
        Navigator.of(context).pushNamedAndRemoveUntil('/home', (r) => false);
      } else {
        Navigator.of(context).pop();
      }
      messenger.showSnackBar(const SnackBar(content: Text('Password berhasil diganti.')));
    } on ApiException catch (e) {
      if (!mounted) return;
      if (e.status == 401) {
        tanganiError(context, e);
        return;
      }
      setState(() {
        _error = e.message;
        _loading = false;
      });
    }
  }

  Future<void> _keluar() async {
    await Api.logout();
    if (!mounted) return;
    Navigator.of(context).pushNamedAndRemoveUntil('/login', (r) => false);
  }

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        title: const Text('Ganti password'),
        automaticallyImplyLeading: !widget.wajib,
        actions: widget.wajib
            ? [TextButton(onPressed: _loading ? null : _keluar, child: const Text('Keluar'))]
            : null,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(widget.wajib
                ? 'Anda wajib mengganti password sebelum memakai aplikasi.'
                : 'Masukkan password baru Anda.'),
            const SizedBox(height: 16),
            TextField(
              controller: _baru,
              obscureText: !_lihat,
              decoration: InputDecoration(
                labelText: 'Password baru (minimal 8 karakter)',
                border: const OutlineInputBorder(),
                suffixIcon: IconButton(
                  icon: Icon(_lihat ? Icons.visibility_off : Icons.visibility),
                  onPressed: () => setState(() => _lihat = !_lihat),
                ),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _ulang,
              obscureText: !_lihat,
              onSubmitted: (_) => _simpan(),
              decoration: const InputDecoration(labelText: 'Ulangi password baru', border: OutlineInputBorder()),
            ),
            if (_error != null) ...[
              const SizedBox(height: 12),
              Text(_error!, style: TextStyle(color: tema.colorScheme.error)),
            ],
            const SizedBox(height: 20),
            FilledButton(
              onPressed: _loading ? null : _simpan,
              child: _loading
                  ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                  : const Text('Simpan password'),
            ),
          ],
        ),
      ),
    );
  }
}