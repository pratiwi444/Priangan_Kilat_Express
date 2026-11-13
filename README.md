<p align="center"> <img src="docs/logo/logo_priangan_kilat_express.png" alt="Logo Priangan Kilat Express" width="420"> </p>

Priangan Kilat Express

1. Ringkasan
Priangan Kilat Express adalah ekspedisi kecil milik Pak Dedi dengan satu hub di Bandung Raya, delapan kurir, dan dua admin. Penggunanya adalah Admin dan Manajer (web desktop), Kurir (APK Android), dan pelanggan umum yang cukup membuka halaman cek resi tanpa login. Masalah utama klien: pencatatan masih di buku sehingga ongkos kirim dan nomor resi tidak konsisten (pernah ada resi ganda), pelanggan harus menelepon untuk menanyakan paketnya, tidak ada catatan siapa mengubah status paket, muatan kurir tidak dibatasi, dan tidak ada catatan setoran uang COD. Alur yang dibangun: Admin mencatat paket di loket, sistem membuat nomor resi unik dan menghitung ongkir otomatis dari zona (lewat layanan Pusat Wilayah) dan berat; paket berjalan Diterima, Transit, Dikirim, lalu Terkirim; kurir melapor hasil antar di APK (maksimal 2 kali gagal, setelahnya hanya Retur); setiap perubahan status tercatat; Admin mencatat setoran COD; Manajer mengatur tarif dan membaca laporan.

2. Teknologi
Bagian -> Server dan tampilan web
Teknologi -> PHP, Laravel
Alasan singkat -> migrasi,validasi, dan middleware peran sudah tersedia sehingga hak akses mudah diperiksa di sisi server, boleh monolith, jadi web dan API dalam satu proyek.

Bagian -> Database
Teknologi -> MYSQL(lewat laragon)
Alasan singkat -> mendukung foreign key dan constraint unik (misalnya no_resi), cocok untuk data relasional paket, kurir, dan setoran.

Bagian -> REST API untuk APK
Teknologi -> Laravel dengan token laravel sanctum
Alasan singkat -> APK memanggil server lewat API bertoken, dan hak akses tetap diperiksa diserver.

Bagian -> APK kurir
Teknologi -> Flutter
Alasan singkat -> satu kode untuk aplikasi android native (bukan webView) dan saya sudah pernah memakainya.

3. Cara menjalankan server 
Langkah ini dibuat untuk perangkat windows dengan Laragon
    1. jalanlan Laragon, lalu klik Start all apache dan MYSQL aktif.
    2. Ambil repositori ke dalam folder laragon\www, lalu masuk ke foldernya
    git clone https://github.com/pratiwi444/Priangan_Kilat_Express.git
    cd ekspedisi
    3. Pasang dependensi
    composer install
    4. Buat database kosong bernama priangan_kilat_express
    mysql -u root -e "CREATE DATABASE priangan_kilat_express"
    5. Siapkan konfigurasi salin .env.example menjadi .env, lalu isi koneksi databse serta alamat layanan pusat wilayah dan API Key (keduanya dokumentasi layanan dihari 1, tdk tertulis dikode)
    copy .env.example .env
    php artisan key:generate

    DB_DATABASE=priangan_kilat_express
    DB_USERNAME=root
    DB_PASSWORD=
    PUSAT_WILAYAH_URL=alamat_layanan_dari_hari_1
    PUSAT_WILAYAH_API_KEY=api_key_dari_hari_1

    6. Jalankan migrasi. Perintah --seed sekaligus membuat akun Admin dan Manajer.
    php artisan migrate:fresh --seed
    7. Impor data awal CSV (pelanggan,kurir, dan paket historis).
    php artisan impor:data-awal
    8. Jalankan seeder data demo (setelah impor CSV).
    php artisan db:seed --class=DemoSeeder
    9. Jalankan server dengan alamat jaringan agar HP bisa menghubunginya.
    php artisan serve --host=0.0.0.0 --port=8000
4. Cara memasang APK