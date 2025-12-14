<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Monitoring Penjualan</title>
    
    {{-- CSS & JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Font & Icons --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-600 antialiased">

    <div class="min-h-screen flex flex-col items-center justify-center p-6 relative overflow-hidden">
        
        {{-- Dekorasi Latar Belakang (Bulatan Halus) --}}
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-emerald-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>

        {{-- KARTU UTAMA --}}
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden transform transition-all hover:scale-[1.01] duration-300">
            
            {{-- Header Berwarna --}}
            <div class="h-32 bg-gradient-to-r from-emerald-500 to-teal-600 flex items-center justify-center relative">
                {{-- Pola Hiasan Tipis (Opsional) --}}
                <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
            </div>

            {{-- Container Logo (Posisi Absolute agar menumpuk di tengah) --}}
            <div class="absolute top-20 left-1/2 transform -translate-x-1/2">
                <div class="w-24 h-24 bg-white p-2 rounded-2xl shadow-lg flex items-center justify-center">
                    <img src="/storage/image/logo_cv.png" alt="Logo CV Bima" class="w-full h-full object-contain">
                </div>
            </div>

            {{-- Konten Text --}}
            <div class="pt-16 pb-8 px-8 text-center mt-4">
                <h2 class="text-sm font-bold text-emerald-600 tracking-wider uppercase mb-1">Selamat Datang di</h2>
                <h1 class="text-2xl font-extrabold text-slate-800 mb-3">CV. BIMA PERRAGA NUSANTARA</h1>
                <p class="text-slate-500 text-sm leading-relaxed mb-8">
                    Sistem terintegrasi untuk memantau stok barang, mencatat penjualan, dan menganalisis data bisnis secara <i>real-time</i>.
                </p>

                {{-- Tombol Login --}}
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" 
                       class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 shadow-lg hover:shadow-emerald-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200 transform hover:-translate-y-1">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-sign-in-alt text-emerald-500 group-hover:text-emerald-400 transition-colors"></i>
                        </span>
                        Masuk ke Dashboard
                    </a>
                @endif
            </div>

            {{-- Footer Kecil --}}
            <div class="bg-slate-50 py-3 text-center border-t border-slate-100">
                <p class="text-xs text-slate-400">
                    &copy; {{ date('Y') }} Sistem Monitoring &bull; Versi 1.0
                </p>
            </div>
        </div>

    </div>

    {{-- Script Animasi Blob (Opsional untuk background bergerak halus) --}}
    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-blob {
            animation: blob 7s infinite;
        }
        .animation-delay-2000 {
            animation-delay: 2s;
        }
    </style>
</body>
</html>