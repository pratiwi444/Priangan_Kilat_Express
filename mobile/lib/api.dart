import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

/// Alamat bawaan: 10.0.2.2 = laptop host bila dijalankan di emulator Android.
/// Di HP sungguhan, ubah lewat ikon pengaturan di layar login (contoh: http://192.168.1.10:8000/api).
const String kBaseDefault = 'http://10.0.2.2:8000/api';

class ApiException implements Exception {
  final String message;
  final int? status;
  final bool wajibGanti;

  ApiException(this.message, {this.status, this.wajibGanti = false});

  @override
  String toString() => message;
}

class Paket {
  final int id;
  final String noResi;
  final String status;
  final String namaPenerima;
  final String teleponPenerima;
  final String? alamatPenerima;
  final String kotaTujuan;
  final double berat;
  final String layanan;
  final bool isCod;
  final int nilaiCod;

  Paket({
    required this.id,
    required this.noResi,
    required this.status,
    required this.namaPenerima,
    required this.teleponPenerima,
    required this.alamatPenerima,
    required this.kotaTujuan,
    required this.berat,
    required this.layanan,
    required this.isCod,
    required this.nilaiCod,
  });

  factory Paket.fromJson(Map<String, dynamic> j) => Paket(
        id: (j['id'] as num).toInt(),
        noResi: (j['no_resi'] ?? '').toString(),
        status: (j['status'] ?? '').toString(),
        namaPenerima: (j['nama_penerima'] ?? '').toString(),
        teleponPenerima: (j['telepon_penerima'] ?? '').toString(),
        alamatPenerima: j['alamat_penerima']?.toString(),
        kotaTujuan: (j['kota_tujuan'] ?? '').toString(),
        berat: (j['berat'] as num?)?.toDouble() ?? 0,
        layanan: (j['layanan'] ?? '').toString(),
        isCod: j['is_cod'] == true || j['is_cod'] == 1,
        nilaiCod: (j['nilai_cod'] as num?)?.toInt() ?? 0,
      );
}

class TugasData {
  final int jumlah;
  final int batas;
  final List<Paket> paket;
  TugasData(this.jumlah, this.batas, this.paket);
}

class CodItem {
  final int id;
  final String noResi;
  final int nilaiCod;
  final bool disetor;
  CodItem(this.id, this.noResi, this.nilaiCod, this.disetor);
}

class CodData {
  final int totalBelumDisetor;
  final List<CodItem> item;
  CodData(this.totalBelumDisetor, this.item);
}

class Api {
  static String base = kBaseDefault;
  static String? token;
  static String nama = '';
  static String kode = '';
  static const Duration _timeout = Duration(seconds: 45); // server lokal (php artisan serve) bisa lambat pada permintaan pertama

  static bool get sudahLogin => token != null && token!.isNotEmpty;

  /// Dipanggil sekali saat aplikasi mulai.
  static Future<void> muat() async {
    final p = await SharedPreferences.getInstance();
    base = p.getString('base') ?? kBaseDefault;
    token = p.getString('token');
    nama = p.getString('nama') ?? '';
    kode = p.getString('kode') ?? '';
  }

  static String rapikanBase(String v) {
    var s = v.trim();
    while (s.endsWith('/')) {
      s = s.substring(0, s.length - 1);
    }
    if (s.isNotEmpty && !s.startsWith('http')) s = 'http://$s';
    return s;
  }

  static Future<void> simpanBase(String v) async {
    base = rapikanBase(v);
    final p = await SharedPreferences.getInstance();
    await p.setString('base', base);
  }

  static Future<void> hapusSesi() async {
    token = null;
    nama = '';
    kode = '';
    final p = await SharedPreferences.getInstance();
    await p.remove('token');
    await p.remove('nama');
    await p.remove('kode');
  }

  // ---------------------------------------------------------------- HTTP

  static Future<Map<String, dynamic>> _kirim(
    String method,
    String path, {
    Map<String, dynamic>? body,
  }) async {
    final uri = Uri.parse('$base$path');
    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      if (sudahLogin) 'Authorization': 'Bearer $token',
    };

    http.Response res;
    try {
      if (method == 'GET') {
        res = await http.get(uri, headers: headers).timeout(_timeout);
      } else {
        res = await http
            .post(uri, headers: headers, body: jsonEncode(body ?? <String, dynamic>{}))
            .timeout(_timeout);
      }
    } on TimeoutException {
      throw ApiException('Server tidak merespons. Periksa koneksi dan alamat server.');
    } on SocketException {
      throw ApiException('Tidak dapat terhubung ke server. Periksa Wi-Fi dan alamat server di pengaturan.');
    } on http.ClientException {
      throw ApiException('Tidak dapat terhubung ke server. Periksa Wi-Fi dan alamat server di pengaturan.');
    } on HandshakeException {
      throw ApiException('Koneksi aman gagal. Gunakan alamat http:// untuk server lokal.');
    }

    Map<String, dynamic> data = <String, dynamic>{};
    if (res.body.trim().isNotEmpty) {
      try {
        final d = jsonDecode(res.body);
        if (d is Map<String, dynamic>) data = d;
      } on FormatException {
        if (res.statusCode >= 200 && res.statusCode < 300) {
          throw ApiException('Respons server tidak valid.');
        }
      }
    }

    if (res.statusCode >= 200 && res.statusCode < 300) return data;

    if (res.statusCode == 401) {
      throw ApiException('Sesi berakhir. Silakan masuk lagi.', status: 401);
    }

    String pesan = (data['message'] ?? 'Terjadi kesalahan (kode ${res.statusCode}).').toString();
    final errors = data['errors'];
    if (errors is Map && errors.isNotEmpty) {
      final first = errors.values.first;
      if (first is List && first.isNotEmpty) pesan = first.first.toString();
    }
    throw ApiException(
      pesan,
      status: res.statusCode,
      wajibGanti: data['wajib_ganti_password'] == true,
    );
  }

  // ---------------------------------------------------------------- endpoint

  /// Mengembalikan true bila kurir wajib ganti password.
  static Future<bool> login(String kode, String password) async {
    final d = await _kirim('POST', '/login', body: {'kode_kurir': kode, 'password': password});
    token = d['token'].toString();
    nama = (d['nama'] ?? '').toString();
    final p = await SharedPreferences.getInstance();
    await p.setString('token', token!);
    await p.setString('nama', nama);
     Api.kode = kode;
    await p.setString('kode', kode);
    return d['wajib_ganti_password'] == true || d['wajib_ganti_password'] == 1;
  }

  static Future<void> logout() async {
    try {
      await _kirim('POST', '/logout');
    } on ApiException {
      // tetap keluar di perangkat meski server tidak terjangkau / token sudah tidak berlaku
    }
    await hapusSesi();
  }

  static Future<void> gantiPassword(String passwordBaru) async {
    await _kirim('POST', '/ganti-password', body: {'password_baru': passwordBaru});
  }

  static Future<TugasData> paketTugas() async {
    final d = await _kirim('GET', '/paket-tugas');
    final list = (d['data'] as List).map((e) => Paket.fromJson(e as Map<String, dynamic>)).toList();
    return TugasData((d['jumlah'] as num).toInt(), (d['batas'] as num).toInt(), list);
  }

  static Future<void> terkirim(int id) async {
    await _kirim('POST', '/paket/$id/terkirim');
  }

  static Future<void> gagalKirim(int id, String catatan) async {
    await _kirim('POST', '/paket/$id/gagal-kirim', body: {'catatan': catatan});
  }

  static Future<CodData> cod() async {
    final d = await _kirim('GET', '/cod');
    final list = (d['data'] as List).map((e) {
      final m = e as Map<String, dynamic>;
      return CodItem((m['id'] as num).toInt(), m['no_resi'].toString(), (m['nilai_cod'] as num).toInt(), m['disetor'] == true);
    }).toList();
    return CodData((d['total_belum_disetor'] as num).toInt(), list);
  }

  /// Uji alamat server tanpa perlu login: login kosong harus dijawab 422 oleh Laravel.
  static Future<void> tesKoneksi() async {
    try {
      await _kirim('POST', '/login', body: <String, dynamic>{});
    } on ApiException catch (e) {
      if (e.status == 422) return; // server Laravel terjangkau
      rethrow;
    }
  }
}