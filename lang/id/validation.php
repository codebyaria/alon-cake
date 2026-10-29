<?php

/*
 * Indonesian validation messages for the rules this app uses. Anything missing
 * falls back to Laravel's English lines. Tone: short and kind (Brand Foundation).
 */

return [
    'boolean' => ':Attribute harus ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'date_format' => ':Attribute harus berformat :format.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'enum' => 'Pilihan :attribute tidak tersedia.',
    'exists' => 'Pilihan :attribute tidak tersedia.',
    'in' => 'Pilihan :attribute tidak tersedia.',
    'integer' => ':Attribute harus berupa angka bulat.',
    'max' => [
        'numeric' => ':Attribute paling besar :max.',
        'string' => ':Attribute paling banyak :max huruf.',
    ],
    'min' => [
        'numeric' => ':Attribute paling kecil :min.',
        'string' => ':Attribute paling sedikit :min huruf.',
    ],
    'numeric' => ':Attribute harus berupa angka.',
    'regex' => 'Format :attribute belum sesuai.',
    'required' => ':Attribute perlu diisi.',
    'required_if' => ':Attribute perlu diisi.',
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah dipakai.',

    'attributes' => [
        'date' => 'tanggal',
        'cake_id' => 'kue',
        'size_id' => 'ukuran',
        'flavor_id' => 'rasa',
        'cake_text' => 'tulisan di kue',
        'card_message' => 'pesan',
        'card_from' => 'nama pengirim',
        'fulfillment' => 'cara ambil',
        'time_slot' => 'jam',
        'delivery_address' => 'alamat pengantaran',
        'customer_name' => 'nama',
        'customer_whatsapp' => 'nomor WhatsApp',
        'payment_method' => 'cara bayar',
    ],
];
