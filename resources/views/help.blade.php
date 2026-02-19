<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bantuan | SITUBA Surakarta</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <header class="bg-white border-b border-gray-100 sticky top-0 z-40">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-gray-700 hover:text-emerald-600 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                <span class="font-semibold">Kembali ke Beranda</span>
            </a>
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-emerald-600 transition-colors">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk Dashboard
            </a>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <section class="bg-white border border-gray-100 rounded-2xl p-6 sm:p-8 shadow-sm">
            <p class="text-xs font-bold tracking-widest text-emerald-600 uppercase mb-2">Pusat Bantuan</p>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Bantuan Penggunaan SITUBA</h1>
            <p class="text-gray-600 mt-3 max-w-3xl">
                Halaman ini membantu pengguna memahami alur dasar penggunaan SITUBA untuk pemantauan skrining dan tindak lanjut di Kota Surakarta.
            </p>

            <div class="mt-8 grid md:grid-cols-2 gap-4">
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                    <h2 class="font-bold text-gray-900 mb-2"><i class="fa-solid fa-users text-emerald-600 mr-2"></i>Untuk Kader</h2>
                    <ul class="text-sm text-gray-600 space-y-1 list-disc pl-5">
                        <li>Login ke dashboard menggunakan akun terdaftar.</li>
                        <li>Input data skrining sesuai form yang tersedia.</li>
                        <li>Pastikan data lokasi dan identitas terisi dengan benar.</li>
                    </ul>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                    <h2 class="font-bold text-gray-900 mb-2"><i class="fa-solid fa-hospital-user text-emerald-600 mr-2"></i>Untuk Puskesmas</h2>
                    <ul class="text-sm text-gray-600 space-y-1 list-disc pl-5">
                        <li>Validasi data skrining dari kader wilayah binaan.</li>
                        <li>Lakukan tindak lanjut pada kasus prioritas.</li>
                        <li>Pantau ringkasan data melalui dashboard.</li>
                    </ul>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                    <h2 class="font-bold text-gray-900 mb-2"><i class="fa-solid fa-building-columns text-emerald-600 mr-2"></i>Untuk Pemda</h2>
                    <ul class="text-sm text-gray-600 space-y-1 list-disc pl-5">
                        <li>Monitor capaian wilayah secara berkala.</li>
                        <li>Verifikasi data pengguna dan pengelolaan kemitraan.</li>
                        <li>Gunakan data untuk evaluasi program TBC.</li>
                    </ul>
                </div>
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-4">
                    <h2 class="font-bold text-gray-900 mb-2"><i class="fa-solid fa-triangle-exclamation text-emerald-600 mr-2"></i>Masalah Umum</h2>
                    <ul class="text-sm text-gray-600 space-y-1 list-disc pl-5">
                        <li>Tidak bisa login: cek nomor/akun dan kredensial.</li>
                        <li>Tampilan tidak update: refresh paksa browser.</li>
                        <li>Data belum muncul: pastikan filter tanggal/wilayah sesuai.</li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 rounded-xl border border-emerald-100 bg-emerald-50 p-5">
                <h2 class="font-bold text-emerald-800 mb-2"><i class="fa-solid fa-headset mr-2"></i>Dukungan</h2>
                <p class="text-sm text-emerald-900/90">
                    Untuk kebutuhan operasional, pengguna dapat berkoordinasi melalui admin/pengelola SITUBA pada instansi terkait.
                    Sertakan informasi akun dan kendala secara ringkas agar proses bantuan lebih cepat.
                </p>
            </div>
        </section>
    </main>

    <footer class="py-8 text-center text-sm text-gray-500">
        © {{ date('Y') }} SITUBA Surakarta.
    </footer>
</body>
</html>
