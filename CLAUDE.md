# Alon Cake — demo portfolio Ari Zain (concept project)

Cake ulang tahun by order. Filosofi: momen penting layak ditunggu.
Nilai: sabar, personal, jujur. Setiap keputusan harus bisa dijelaskan dari nilai ini.

## Stack
Laravel + Inertia React (TypeScript) + Tailwind + Filament (admin).
Demo tanpa integrasi: pembayaran disimulasikan, WhatsApp hanya pratinjau pesan, data dari seeder.

## Visual
- Warna: krim #F6F0E6 (latar), putih tulang #FBF8F3 (kartu), cokelat tua #4A2E22 (teks/garis), aksen dusty pink #C9938B (jarang)
- Font: Cormorant Garamond (heading, hanya >= 24px), DM Sans (body, label, tombol, harga)
- Teks minim, whitespace lebar, foto diam (tanpa video), tanpa font mesin ketik/retro/pixel
- Elemen khas: garis piping bergelombang tipis

## Interaksi
- Momen khas: lingkaran tulisan tangan tergambar di sekitar tanggal yang dipilih (kalender + konfirmasi)
- Section proses: kue tersusun lapis demi lapis saat scroll
- Hero: baris "Hari ini di dapur: ..." dari data asli
- Gerak: 600–1000 ms ease-out, hanya transform/opacity, hormati prefers-reduced-motion

## Aturan produk
- Pre-order minimal H-3, kapasitas per hari, Minggu libur
- Alur: tanggal -> kue -> ukuran/rasa/tulisan -> kartu pesan -> ambil/antar & bayar -> konfirmasi
- Tidak ada keranjang, akun, flash sale, countdown, atau kata "buruan"
- Bahasa: Indonesia (default) + English (/en)

## Referensi
- Brand Foundation: https://claude.ai/artifact/7EhBYfSDdpPbS7JKouTQyE
- Wireframe & style tile: https://claude.ai/artifact/BkhTm7j4Xy2amREhAmm5jT
