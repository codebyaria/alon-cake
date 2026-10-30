# Alon Cake

Kue ulang tahun by order, dibuat satu per satu, tiga hari sebelum hari-H.
Filosofinya sederhana: momen penting layak ditunggu.

Ini proyek konsep untuk portofolio [Ari Zain](https://github.com/codebyaria). Bukan toko sungguhan: pembayaran disimulasikan, pesan WhatsApp hanya pratinjau, dan semua data berasal dari seeder.

**Demo:** https://aloncake.arizain.my.id (data diulang setiap malam pukul 03.00 WIB)

## Yang bisa dicoba

- **Beranda** (`/`, `/en`): baris "Hari ini di dapur" yang dihitung dari pesanan sungguhan, kue yang tersusun lapis demi lapis saat di-scroll, dan kalender ketersediaan dua minggu ke depan.
- **Alur pesan** (`/pesan`): tanggal → kue → ukuran, rasa, tulisan → kartu pesan → ambil/antar & bayar → konfirmasi. Tanggal yang dipilih dilingkari dengan garis tulisan tangan, dan halaman konfirmasi menyediakan file kalender `.ics`.
- **Admin dapur** (`/admin`): pesanan per hari dan tahapnya, kapasitas harian, menu, ukuran dan rasa.

## Aturan produk

- Pesan paling cepat H-3, karena sponge dipanggang H-2 dan kue dihias H-1.
- Kapasitas dibatasi per hari dan dicek ulang saat membayar, jadi satu tanggal tidak bisa kelebihan pesanan.
- Minggu libur. Dapur juga bisa ditutup untuk tanggal tertentu.
- Tidak ada keranjang, akun pelanggan, flash sale, atau hitung mundur.
- Bahasa Indonesia sebagai bawaan, English di `/en`.

## Stack

- Laravel 13, PHP 8.5, SQLite
- Inertia 3 + React 19 + TypeScript, Tailwind CSS 4
- Filament 5 untuk admin
- Pest untuk tes

## Menjalankan di komputer sendiri

Butuh PHP 8.5, Composer, dan Node 22.

```bash
composer run setup
php artisan storage:link
php artisan db:seed
composer run dev
```

Buka http://localhost:8000. Admin ada di http://localhost:8000/admin dengan akun demo `admin@aloncake.test` / `password`.

Data demo dibuat relatif terhadap hari ini, jadi kalender selalu terisi. Untuk mengulang dari awal:

```bash
php artisan migrate:fresh --seed
```

## Tes

```bash
php artisan test
```

## Catatan desain

- Warna: krim `#F6F0E6`, putih tulang `#FBF8F3`, cokelat tua `#4A2E22`, dan dusty pink `#C9938B` yang dipakai jarang.
- Huruf: Cormorant Garamond untuk judul (minimal 24px) dan DM Sans untuk sisanya.
- Gerak: 600–1000 ms ease-out, hanya `transform` dan `opacity`, dan mati sendiri kalau perangkat meminta gerak dikurangi.
- Semua foto dibuat dengan AI untuk keperluan demo, dan ditulis begitu juga di footer situs. Foto disimpan sebagai WebP: hero dan foto kartu dalam dua ukuran (600 dan 1100 px), foto kue 800 px, masing-masing di bawah 50 KB.
- Menu dibedakan dari hiasannya; rasa (vanila, cokelat, lemon) dipilih terpisah di langkah 3.
