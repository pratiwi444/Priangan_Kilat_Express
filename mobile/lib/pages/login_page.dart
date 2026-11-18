import 'package:flutter/material.dart';

import '../api.dart';
import 'pengaturan_page.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _kode = TextEditingController();
  final _password = TextEditingController();
  bool _loading = false;
  bool _lihat = false;
  String? _error;

  @override
  void dispose() {
    _kode.dispose();
    _password.dispose();
    super.dispose();
  }

  Future<void> _masuk() async {
    final kode = _kode.text.trim();
    final pw = _password.text;
    if (kode.isEmpty || pw.isEmpty) {
      setState(() => _error = 'Kode kurir dan password wajib diisi.');
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final wajibGanti = await Api.login(kode, pw);
      if (!mounted) return;
      Navigator.of(context).pushReplacementNamed(wajibGanti ? '/ganti' : '/home');
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.message;
        _loading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Scaffold(
      appBar: AppBar(
        actions: [
          IconButton(
            tooltip: 'Pengaturan server',
            icon: const Icon(Icons.settings),
            onPressed: () => Navigator.of(context).push(
              MaterialPageRoute<void>(builder: (_) => const PengaturanPage()),
            ),
          ),
        ],
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 420),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Icon(Icons.local_shipping, size: 64, color: tema.colorScheme.primary),
                const SizedBox(height: 8),
                Text('Kurir PKE', textAlign: TextAlign.center, style: tema.textTheme.headlineMedium),
                Text('Priangan Kilat Express', textAlign: TextAlign.center, style: tema.textTheme.bodyMedium),
                const SizedBox(height: 32),
                TextField(
                  controller: _kode,
                  textInputAction: TextInputAction.next,
                  autocorrect: false,
                  decoration: const InputDecoration(
                    labelText: 'Kode kurir',
                    hintText: 'contoh: KUR-02',
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _password,
                  obscureText: !_lihat,
                  onSubmitted: (_) => _masuk(),
                  decoration: InputDecoration(
                    labelText: 'Password',
                    border: const OutlineInputBorder(),
                    suffixIcon: IconButton(
                      icon: Icon(_lihat ? Icons.visibility_off : Icons.visibility),
                      onPressed: () => setState(() => _lihat = !_lihat),
                    ),
                  ),
                ),
                if (_error != null) ...[
                  const SizedBox(height: 12),
                  Text(_error!, style: TextStyle(color: tema.colorScheme.error)),
                ],
                const SizedBox(height: 20),
                FilledButton(
                  onPressed: _loading ? null : _masuk,
                  child: _loading
                      ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                      : const Text('Masuk'),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}