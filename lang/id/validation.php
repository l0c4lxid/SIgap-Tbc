<?php

return [
    'confirmed' => 'Konfirmasi :attribute tidak sesuai.',
    'required' => ':Attribute wajib diisi.',
    'string' => ':Attribute harus berupa teks.',
    'max' => [
        'string' => ':Attribute maksimal :max karakter.',
    ],
    'min' => [
        'string' => ':Attribute minimal :min karakter.',
    ],
    'unique' => ':Attribute sudah digunakan.',
    'exists' => ':Attribute tidak ditemukan atau sudah tidak aktif.',
    'in' => ':Attribute tidak valid.',
    'not_in' => ':Attribute tidak valid.',
    'enum' => ':Attribute tidak valid.',
    'password' => [
        'letters' => ':Attribute harus mengandung huruf.',
        'mixed' => ':Attribute harus mengandung huruf besar dan kecil.',
        'numbers' => ':Attribute harus mengandung angka.',
        'symbols' => ':Attribute harus mengandung simbol.',
        'uncompromised' => ':Attribute terdeteksi pada kebocoran data. Gunakan kata sandi lain.',
    ],
    'attributes' => [
        'name' => 'nama',
        'phone' => 'nomor HP',
        'password' => 'kata sandi',
        'role' => 'peran',
        'kelurahan_name' => 'nama kelurahan',
        'kelurahan_address' => 'alamat kelurahan',
        'kelurahan_puskesmas_id' => 'puskesmas pembina',
        'puskesmas_name' => 'nama puskesmas',
        'puskesmas_address' => 'alamat puskesmas',
        'puskesmas_kelurahan_id' => 'kelurahan mitra',
        'kader_puskesmas_id' => 'puskesmas induk',
    ],
];
