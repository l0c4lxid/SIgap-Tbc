<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi | SITUBA Surakarta</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <header class="bg-white border-b border-gray-100 sticky top-0 z-40">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-gray-700 hover:text-emerald-600 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                <span class="font-semibold">Kembali ke Beranda</span>
            </a>
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-emerald-600 transition-colors">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk Dashboard
            </a>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <section class="bg-white border border-gray-100 rounded-2xl p-6 sm:p-8 shadow-sm">
            <p class="text-xs font-bold tracking-widest text-emerald-600 uppercase mb-2">Dokumen Publik</p>
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Kebijakan Privasi SITUBA</h1>
            <p class="text-sm text-gray-500 mt-3">Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>

            <div class="mt-8 space-y-8 text-sm sm:text-base leading-relaxed text-gray-700">
                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-2"><i class="fa-solid fa-shield-halved text-emerald-600 mr-2"></i>1. Ruang Lingkup</h2>
                    <p>
                        SITUBA adalah sistem informasi untuk mendukung pemantauan skrining, tindak lanjut, dan kolaborasi antar kader, puskesmas, kelurahan, dan pemerintah daerah di Kota Surakarta.
                        Kebijakan ini menjelaskan cara data diproses dalam penggunaan layanan SITUBA.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-2"><i class="fa-solid fa-database text-emerald-600 mr-2"></i>2. Data yang Diproses</h2>
                    <ul class="list-disc pl-6 space-y-1">
                        <li>Data identitas pengguna aplikasi (akun petugas/kader).</li>
                        <li>Data operasional skrining dan tindak lanjut kasus.</li>
                        <li>Data aktivitas sistem untuk keamanan dan audit internal.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-2"><i class="fa-solid fa-bullseye text-emerald-600 mr-2"></i>3. Tujuan Penggunaan Data</h2>
                    <ul class="list-disc pl-6 space-y-1">
                        <li>Mendukung proses pemantauan dan pelaporan program TBC.</li>
                        <li>Meningkatkan koordinasi lintas peran dalam alur SITUBA.</li>
                        <li>Menjaga kualitas layanan, keandalan sistem, dan keamanan aplikasi.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-2"><i class="fa-solid fa-lock text-emerald-600 mr-2"></i>4. Keamanan dan Kerahasiaan</h2>
                    <p>
                        SITUBA menerapkan kontrol akses berbasis peran, pencatatan aktivitas, dan praktik keamanan sistem yang relevan untuk menjaga kerahasiaan data.
                        Akses data dibatasi sesuai tanggung jawab pengguna dalam aplikasi.
                    </p>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-2"><i class="fa-solid fa-user-check text-emerald-600 mr-2"></i>5. Hak dan Tanggung Jawab Pengguna</h2>
                    <ul class="list-disc pl-6 space-y-1">
                        <li>Pengguna wajib menjaga kerahasiaan akun dan kredensial akses.</li>
                        <li>Pengguna bertanggung jawab atas akurasi data yang diinput.</li>
                        <li>Pengguna tidak diperkenankan menyebarluaskan data tanpa otorisasi resmi.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-lg font-bold text-gray-900 mb-2"><i class="fa-solid fa-pen-to-square text-emerald-600 mr-2"></i>6. Perubahan Kebijakan</h2>
                    <p>
                        Kebijakan privasi dapat diperbarui untuk menyesuaikan kebutuhan operasional, regulasi, atau peningkatan sistem. Versi terbaru akan ditampilkan pada halaman ini.
                    </p>
                </section>
            </div>
        </section>
    </main>

    <footer class="py-8 text-center text-sm text-gray-500">
        © {{ date('Y') }} SITUBA Surakarta.
    </footer>
</body>
</html>
