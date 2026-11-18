<p align="center">
  <img src="docs/logo/logo_priangan_kilat_express.png" alt="Logo Priangan Kilat Express" width="420">
</p>

# Priangan Kilat Express

Sistem pencatatan dan pelacakan paket ekspedisi. UKK RPL 2026/2027, Soal 2 Varian B (Ekspedisi).

## 1. Ringkasan

Priangan Kilat Express adalah ekspedisi kecil milik Pak Dedi dengan satu hub di Bandung Raya, delapan kurir, dan dua admin. Penggunanya adalah Admin dan Manajer (web desktop), Kurir (APK Android), dan pelanggan umum yang cukup membuka halaman cek resi tanpa login. Masalah utama klien: pencatatan masih di buku sehingga ongkos kirim dan nomor resi tidak konsisten (pernah ada resi ganda), pelanggan harus menelepon untuk menanyakan paketnya, tidak ada catatan siapa mengubah status paket, muatan kurir tidak dibatasi, dan tidak ada catatan setoran uang COD. Alur yang dibangun: Admin mencatat paket di loket, sistem membuat nomor resi unik dan menghitung ongkir otomatis dari zona (lewat layanan Pusat Wilayah) dan berat; paket berjalan Diterima, Transit, Dikirim, lalu Terkirim; kurir melapor hasil antar di APK (maksimal 2 kali gagal, setelahnya hanya Retur); setiap perubahan status tercatat; Admin mencatat setoran COD; Manajer mengatur tarif dan membaca laporan.

## 2. Teknologi

| Bagian | Teknologi | Alasan singkat |
|---|---|---|
| Server dan tampilan web | PHP 8.3, Laravel 13 (Blade + Bootstrap lokal) | Migrasi, validasi, dan middleware peran sudah tersedia sehingga hak akses diperiksa di sisi server. Web dan API dalam satu proyek (monolith). |
| Database | MySQL (lewat Laragon) | Mendukung foreign key dan constraint unik (misalnya `no_resi`, satu setoran per paket), cocok untuk data relasional paket, kurir, dan setoran. |
| REST API untuk APK | Laravel + token Laravel Sanctum | APK memanggil server lewat API bertoken; hak akses tetap diperiksa di server. |
| APK Kurir | Flutter (Dart), paket `http` dan `shared_preferences` | Satu kode untuk aplikasi Android native (bukan WebView) dan saya sudah pernah memakainya. APK dibangun lewat GitHub Actions (`.github/workflows/build-apk.yml`) supaya tidak butuh Android SDK di laptop, lalu hasilnya disalin ke folder `apk/`. |

## 3. Cara menjalankan server

Langkah ini dibuat untuk **Windows dengan Laragon** (PHP 8.3, MySQL). Layanan Pusat Wilayah butuh internet; tanpa internet sistem tetap jalan dengan zona manual (lihat bagian 7).

1. Jalankan Laragon, lalu klik Start All agar Apache dan MySQL aktif.
2. Ambil repositori ke dalam folder `laragon\www`, lalu masuk ke foldernya.
   ```bash
   git clone https://github.com/USERNAME/ekspedisi.git
   cd ekspedisi
   ```
3. Pasang dependensi.
   ```bash
   composer install
   ```
4. Buat database kosong bernama `Priangan_Kilat_Express`.
   ```bash
   mysql -u root -e "CREATE DATABASE Priangan_Kilat_Express"
   ```
5. Siapkan konfigurasi. Salin `.env.example` menjadi `.env`, lalu isi koneksi database serta alamat layanan Pusat Wilayah dan API key (dari dokumen "Info API Key"; tidak ditulis di kode dan `.env` tidak di-commit).
   ```bash
   copy .env.example .env
   php artisan key:generate
   ```
   ```env
   DB_DATABASE=Priangan_Kilat_Express
   DB_USERNAME=root
   DB_PASSWORD=
   PUSAT_WILAYAH_DRIVER=http
   PUSAT_WILAYAH_URL=https://ukksmkn1katapang.next-it.co.id/ekspedisi
   PUSAT_WILAYAH_API_KEY=isi_dari_dokumen_api_key
   ```
6. Jalankan migrasi. Perintah `--seed` sekaligus mengisi master zona dan tarif (dari `master_zona_tarif.csv`) serta membuat akun Admin dan Manajer.
   ```bash
   php artisan migrate:fresh --seed
   ```
7. Impor data awal CSV (kurir, pelanggan, dan paket historis). Aman dijalankan ulang.
   ```bash
   php artisan impor:data-awal
   ```
8. Jalankan seeder data demo (setelah impor CSV). Aman dijalankan ulang, tidak menggandakan data.
   ```bash
   php artisan db:seed --class=DemoSeeder
   ```
9. Jalankan server dengan alamat jaringan agar HP bisa menghubunginya. Bila HP tidak bisa terhubung, izinkan port 8000 di Windows Firewall.
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

Web dibuka di `http://127.0.0.1:8000` (halaman awal adalah cek resi publik; petugas masuk lewat halaman `/login`).

Tes otomatis (perhitungan ongkir dan biaya COD): `php artisan test`.

## 4. Cara memasang APK

1. Berkas APK ada di folder `apk/` dengan nama `priangan-kilat-express.apk`.
2. Salin ke HP Android 8.0 (API 26) atau lebih baru, lalu pasang. Izinkan pemasangan dari sumber tidak dikenal bila diminta.
3. Atur alamat server yang dipanggil APK: di layar login ketuk **ikon pengaturan (roda gigi)** di pojok kanan atas, isi **Alamat server**, misalnya `http://192.168.1.10:8000`. Isi dengan alamat IP laptop di jaringan yang sama dengan HP (cek dengan perintah `ipconfig`, bagian IPv4). Jangan memakai `localhost`, karena dari HP itu menunjuk ke HP sendiri, bukan ke server. Bila tidak ada Wi-Fi, hubungkan laptop ke hotspot HP.

## 5. Akun uji

Satu akun untuk setiap peran. Password di bawah hanya untuk uji.

| Peran | Dipakai di | Username atau kode | Password (sementara) |
|---|---|---|---|
| Admin | Web | `admin` | `admin12345` |
| Manajer | Web | `manajer` | `manajer12345` |
| Kurir | APK | `KUR-01` | `kurir12345` (wajib diganti saat login pertama) |

Semua kurir dari data awal (KUR-01 sampai KUR-08) memakai password sementara `kurir12345` dan wajib menggantinya saat login pertama. Kurir yang disiapkan seeder demo: **KUR-02** (tepat 20 paket Dikirim).

## 6. Letak berkas

| Isi | Letak |
|---|---|
| Migrasi | `database/migrations/` |
| Berkas CSV (data awal dan data seeder dari klien/penguji) | `database/data/` (`kurir.csv`, `pelanggan.csv`, `paket_historis.csv`, `master_zona_tarif.csv`, `seed_paket_dikirim.csv`) |
| Perintah impor data awal | `app/Console/Commands/ImporDataAwal.php` (`php artisan impor:data-awal`) |
| Logika impor (dipakai perintah dan halaman web Impor CSV) | `app/Services/ImporCsv.php` |
| Seeder zona/tarif, akun Admin dan Manajer | `database/seeders/DatabaseSeeder.php` |
| Seeder data demo | `database/seeders/DemoSeeder.php` |
| Aplikasi kurir (Flutter) | `mobile/` (kode di `mobile/lib/`) |
| Build APK otomatis | `.github/workflows/build-apk.yml` |
| REST API untuk APK | `routes/api.php`, `app/Http/Controllers/Api/KurirApiController.php` |
| ERD dan UML | `docs/erd/`, `docs/uml/` |

Perintah menjalankan seeder data demo (aman dijalankan ulang, tidak menggandakan data):

```bash
php artisan db:seed --class=DemoSeeder
```

Keadaan yang disiapkan seeder: kurir **KUR-02** memiliki tepat 20 paket berstatus Dikirim (4 dari data awal ditambah 16 dari `seed_paket_dikirim.csv`). Seeder tidak membuat penugasan atau perubahan status lain.

## 7. Keputusan saya

| Hal | Keputusan |
|---|---|
| Cara menyimpan perubahan harga agar paket lama tidak berubah | Tarif per kg zona dan ongkir disalin ke data paket saat paket diinput (kolom `tarif_per_kg` dan `ongkir` di tabel `paket`). Laporan pendapatan memakai angka yang tersimpan di paket, bukan tarif zona yang sekarang. |
| Aturan format telepon selain tanpa huruf dan minimal 10 digit | Hanya angka, panjang 10 sampai 13 digit, dan disimpan dalam format `08xxxxxxxxxx`. Input berawalan `+62` atau `62` diubah menjadi `0`. |
| Zona dan layanan Pusat Wilayah | Server memanggil layanan (API key di `.env`). Di form Tambah Paket, tombol **Cek zona & biaya** menampilkan zona dan pratinjau ongkir, biaya COD, dan total. Bila layanan mati, API key ditolak, atau kota tidak dikenal (`zona: null`), Admin memilih zona manual dan paket ditandai "manual". Server menghitung ulang saat menyimpan. |
| Data awal: nomor resi tercatat dua kali | Digabung menjadi satu; baris pertama yang muncul di berkas yang dipertahankan. Jumlah yang digabung dilaporkan pada hasil impor (3 baris pada data penguji). |
| Data awal: paket tanpa data berat | Tetap diimpor dan ditandai **"perlu verifikasi"** (ikon `?` dan lencana di daftar dan detail paket, ada filter "Perlu verifikasi"). Ongkirnya dikosongkan karena belum bisa dihitung. |
| Data awal: nomor telepon berformat +62..., 62..., dan 08... | Dirapikan ke format `08xxxxxxxxxx`. Semua nomor tetap dapat diimpor; nomor yang tidak bisa dirapikan disimpan apa adanya, bukan menolak baris. |
| Data awal: penulisan nama tidak seragam | Dirapikan menjadi huruf awal kapital pada tiap kata, dan spasi berlebih dibuang. |
| Data awal: kolom `kota` pada `pelanggan.csv` | Diabaikan (tabel pelanggan hanya menyimpan alamat). |
| Data awal: paket historis | Zona diambil dari Pusat Wilayah dan ongkir dihitung saat impor bila berat ada. Alamat penerima kosong (CSV tidak memilikinya). Tanggal masuk = kolom `tanggal_terima`. Paket berstatus Retur diberi jumlah gagal 2. Paket COD berstatus Terkirim dicatat sudah disetor. Kurir pada paket disimpan sesuai CSV. |
| Impor ulang | Data yang sudah ada dilewati dan tidak ditimpa (resi, kode kurir, atau telepon yang sama). Baris yang rusak dilewati dan dilaporkan; impor tidak berhenti. |
| Password sementara kurir data awal | `kurir12345`, wajib diganti saat login pertama (lihat bagian 5). Reset oleh Admin memakai password sementara baru dan sesi lama kurir dicabut. |
| Penugasan dan kapasitas | Admin menugaskan paket Transit (atau Gagal Kirim yang belum 2 kali) ke kurir aktif. Batas 20 paket Dikirim per kurir dihitung di server dalam transaksi. Kurir yang masih membawa paket Dikirim tidak bisa dinonaktifkan. |
| Cek resi publik | Menampilkan riwayat (waktu, status, catatan) dan telepon yang disamarkan di server. Catatan penugasan tidak memuat kode atau nama kurir. |

## 8. Lembar self-check

Klaim di bawah diisi setelah saya menguji sendiri tiap butir.

| No | Butir penerimaan | Klaim (Ya/Tidak) | Langkah uji singkat |
|---|---|---|---|
| 1 | Input paket (pengirim, penerima, kota tujuan, berat, layanan), nomor resi otomatis, unik, reset harian; nomor telepon divalidasi | Ya | Admin > Paket > Tambah dua paket: resi berurutan `PKE-YYMMDD-0001`, `-0002`. Telepon berisi huruf atau kurang dari 10 digit ditolak dengan pesan. |
| 2 | Ongkir otomatis dari zona dan berat (dibulatkan ke atas, minimum 1 kg), Express +50%; ongkir paket lama tidak berubah bila tarif per kg zona diubah (butir inti) | Ya | Bandung 1,2 kg Reguler = Rp16.000, Express = Rp24.000; 0,3 kg = Rp8.000. Manajer ubah tarif Zona 1, lalu buka paket lama: ongkir dan laporan tetap. |
| 3 | Status berjenjang, loncat ditolak, Terkirim dan Retur final (butir inti) | Ya | Paket Diterima hanya bisa ke Transit; Dikirim, Terkirim, atau Retur langsung ditolak. Paket Terkirim atau Retur tidak bisa diubah lagi. |
| 4 | Riwayat setiap perubahan status (waktu, pelaku: Admin atau Kurir, catatan) | Ya | Ubah status beberapa kali, buka detail paket: tabel Riwayat Status. |
| 5 | Gagal kirim maksimal 2 kali; setelah gagal kedua, kirim ulang ditolak dan status berikutnya hanya Retur (diubah Admin) | Ya | Gagalkan satu paket 2 kali (APK): penugasan ulang ditolak, hanya Retur. Retur sebelum gagal 2 kali ditolak. |
| 6 | Penugasan ke kurir (status menjadi Dikirim), batas 20 paket berstatus Dikirim per kurir pada saat yang sama; kurir nonaktif tidak bisa ditugaskan | Ya | Tugaskan paket Transit ke `KUR-02` (sudah 20): ditolak. Nonaktifkan kurir lalu coba tugaskan: ditolak. |
| 7 | Cek resi publik tanpa login, telepon disamarkan di server | Ya | Buka `/cek-resi` di jendela tanpa login (atau tombol Cek Resi di halaman login); telepon tersamar (`0812789xxxx`). Lihat sumber halaman: nomor lengkap, alamat, pengirim, COD tidak ada. |
| 8 | Biaya COD 2% (minimum Rp2.000) dibayar pengirim; setoran COD dicatat Admin per paket (hanya paket COD Terkirim, tidak dua kali); rekap COD per kurir belum/sudah disetor (di laporan Manajer) | Ya | Input paket COD nilai 250000: biaya Rp5.000; nilai 25000: biaya Rp2.000. Admin > Setoran COD: catat setoran; kedua kalinya ditolak; paket non-COD atau belum Terkirim ditolak. Rekap ada di Laporan Manajer. |
| 9 | Laporan pengiriman dan pendapatan per zona per periode (menurut tanggal masuk); hak akses: tarif hanya bisa diubah Manajer, laporan hanya Manajer (peran lain ditolak, juga bila halaman dibuka lewat alamat langsung), data pelanggan dan kurir hanya diubah Admin | Ya | Login Manajer: Laporan per periode (atur dari 01/10/2026 untuk data awal). Login Admin, buka `/manajer/laporan` langsung: 403. Admin ubah tarif: ditolak. Manajer buka `/admin/pelanggan/create`: 403. |
| 10 | Data awal CSV diimpor: resi ganda menjadi satu, paket tanpa berat bertanda "perlu verifikasi", telepon dirapikan | Ya | `migrate:fresh --seed` lalu `impor:data-awal`: 60 paket masuk dari 63 baris (3 resi ganda digabung). Daftar paket, filter "Perlu verifikasi": 5 paket berikon `?`. Telepon seragam `08...`. Impor juga bisa lewat menu Admin > Impor CSV. |
| 11 | Integrasi Pusat Wilayah (dengan API key), fallback zona manual bila layanan mati atau kota tidak dikenal | Ya | Kota Bandung: zona terisi otomatis. Ubah satu huruf API key di `.env` (lalu `php artisan config:clear`) atau isi kota "Atlantis": zona dipilih manual, paket bertanda "manual", tanpa crash. |
| 12 | Akun kurir lengkap (APK): dibuat Admin, login dengan kode kurir (KUR-01 dst.), password minimal 8 karakter, wajib ganti password saat login pertama, ganti password, lupa password direset Admin (wajib ganti saat login berikutnya), logout | **ISI: Ya/Tidak** (setelah uji di HP) | Admin buat kurir. Login APK, wajib ganti password; kurang dari 8 karakter ditolak. Admin reset, login lagi, wajib ganti. Logout. |
| 13 | Kurir (APK) melihat paket berstatus Dikirim yang ditugaskan kepadanya dan mengubahnya menjadi Terkirim atau Gagal Kirim dengan catatan; paket kurir lain tidak bisa dibuka atau diubah | **ISI: Ya/Tidak** (setelah uji di HP) | Login kurir: hanya paket Dikirim miliknya. Ubah ke Terkirim; Gagal Kirim tanpa catatan ditolak. Paket kurir lain: ditolak (403). |
| 14 | Kurir (APK) melihat paket COD berstatus Terkirim yang diantarnya dan total nilai COD yang belum disetor | **ISI: Ya/Tidak** (setelah uji di HP) | Buka tab COD di APK; setelah Admin catat setoran, total belum disetor berkurang. |

Fitur opsional yang dikerjakan:

| No | Fitur opsional | Dikerjakan (Ya/Tidak) |
|---|---|---|
| O1 | Berat volumetrik: panjang × lebar × tinggi (cm) ÷ 6000, dipakai bila lebih besar dari berat aktual | Tidak |
| O2 | Asuransi opsional sebesar 0,5% dari nilai barang | Tidak |
| O3 | Laporan performa kurir: persentase paket terkirim dan gagal | Tidak |
| O4 | Penanda paket tertahan lebih dari 3 hari di status Transit | Tidak |
| O5 | Reset password lewat email dengan server email tiruan lokal (mis. Mailpit) | Tidak |

## 9. Belum selesai / keterbatasan

- Form Tambah Paket berupa satu paket per form (wireframe awal berupa tabel banyak baris).
- Penugasan kurir dilakukan per paket dari daftar penugasan/detail paket.
- Zona otomatis membutuhkan internet ke layanan Pusat Wilayah; tanpa internet Admin memilih zona manual.
- APK ditandatangani dengan kunci debug bawaan Flutter (cukup untuk pemasangan manual, bukan untuk toko aplikasi) dan dibangun lewat GitHub Actions; berkas yang dipakai wawancara adalah `apk/priangan-kilat-express.apk`.
- Alamat penerima untuk paket data awal kosong karena CSV tidak memilikinya.
- Fitur opsional O1 sampai O5 tidak dikerjakan.
- (Tulis di sini jujur butir yang belum berjalan sempurna setelah uji akhir.)

## 10. Referensi dan Bantuan

| Alat atau sumber | Dipakai untuk bagian | Yang saya ubah dari hasilnya |
|---|---|---|
| Claude (AI) | Draf README, ERD, diagram UML, wireframe UI, logo, bantuan menulis dan memperbaiki error kode Laravel (migrasi, seeder, impor CSV, REST API) dan penjelasan alur | Semua kode saya tempel, jalankan, dan uji sendiri; saya sesuaikan dengan dokumen soal, data CSV dari penguji, dan hasil uji (mis. nama variabel `.env`, aturan zona, tampilan) sebelum dipakai |
| Dokumentasi Laravel, Laravel Sanctum, Flutter, dan Bootstrap | Referensi sintaks dan pemakaian paket | Disesuaikan dengan kebutuhan sistem ini |