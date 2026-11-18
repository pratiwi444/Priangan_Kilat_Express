import 'package:flutter/material.dart';

import '../api.dart';
import 'ganti_password_page.dart';
import 'pengaturan_page.dart';

class ProfilTab extends StatelessWidget {
  final VoidCallback onKeluar;
  const ProfilTab({super.key, required this.onKeluar});

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    final nama = Api.nama.isEmpty ? '-' : Api.nama;
    final inisial = Api.nama.isEmpty ? '?' : Api.nama.trim()[0].toUpperCase();

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        const SizedBox(height: 8),
        Center(
          child: CircleAvatar(
            radius: 40,
            backgroundColor: tema.colorScheme.primaryContainer,
            child: Text(inisial, style: tema.textTheme.headlineMedium),
          ),
        ),
        const SizedBox(height: 12),
        Center(child: Text(nama, style: tema.textTheme.titleLarge)),
        if (Api.kode.isNotEmpty) Center(child: Text(Api.kode, style: tema.textTheme.bodyMedium)),
        const SizedBox(height: 24),
        Card(
          child: Column(
            children: [
              ListTile(
                leading: const Icon(Icons.lock_outline),
                title: const Text('Ganti password'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute<void>(builder: (_) => const GantiPasswordPage(wajib: false)),
                ),
              ),
              const Divider(height: 1),
              ListTile(
                leading: const Icon(Icons.dns_outlined),
                title: const Text('Alamat server'),
                subtitle: Text(Api.base),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => Navigator.of(context).push(
                  MaterialPageRoute<void>(builder: (_) => const PengaturanPage()),
                ),
              ),
              const Divider(height: 1),
              const ListTile(
                leading: Icon(Icons.info_outline),
                title: Text('Versi aplikasi'),
                subtitle: Text('1.0.0 - Priangan Kilat Express'),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),
        OutlinedButton.icon(
          onPressed: onKeluar,
          icon: const Icon(Icons.logout),
          label: const Text('Keluar'),
        ),
      ],
    );
  }
}