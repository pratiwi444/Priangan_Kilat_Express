import 'package:flutter/material.dart';

import 'api.dart';

/// 1250000 -> Rp1.250.000
String rupiah(int n) {
  final s = n.abs().toString();
  final b = StringBuffer();
  for (var i = 0; i < s.length; i++) {
    if (i > 0 && (s.length - i) % 3 == 0) b.write('.');
    b.write(s[i]);
  }
  final minus = n < 0 ? '-' : '';
  return 'Rp$minus$b';
}

void tampilPesan(BuildContext context, String pesan) {
  ScaffoldMessenger.of(context)
    ..hideCurrentSnackBar()
    ..showSnackBar(SnackBar(content: Text(pesan)));
}

/// Penanganan error API yang seragam:
/// - 401 (token tidak berlaku): kembali ke layar login
/// - 403 wajib ganti password: ke layar ganti password
/// - lainnya: tampilkan pesan
void tanganiError(BuildContext context, Object e) {
  final messenger = ScaffoldMessenger.of(context);
  final nav = Navigator.of(context);

  if (e is ApiException) {
    if (e.status == 401) {
      Api.hapusSesi();
      nav.pushNamedAndRemoveUntil('/login', (r) => false);
      messenger.showSnackBar(SnackBar(content: Text(e.message)));
      return;
    }
    if (e.wajibGanti) {
      nav.pushNamedAndRemoveUntil('/ganti', (r) => false);
      return;
    }
    messenger.showSnackBar(SnackBar(content: Text(e.message)));
    return;
  }
  messenger.showSnackBar(SnackBar(content: Text('Terjadi kesalahan: $e')));
}