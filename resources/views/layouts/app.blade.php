<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Monitoring')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    
    {{-- Google Font: Inter --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-600 antialiased" x-data="{ sidebarOpen: false }">
    
    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar Backdrop (Mobile) --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false" 
             x-transition:enter="transition-opacity ease-linear duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition-opacity ease-linear duration-300" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 z-20 bg-slate-900 bg-opacity-20 backdrop-blur-sm lg:hidden"></div>

        {{-- Sidebar --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-30 w-64 overflow-y-auto transition-transform duration-300 transform bg-white border-r border-slate-100 lg:translate-x-0 lg:static lg:inset-0 flex flex-col justify-between shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
            
            <div>
                {{-- LOGO HEADER (SUDAH DIPERBAIKI) --}}
                <div class="flex items-center justify-center p-6 mb-2">
                    <div class="flex items-center gap-3">
                       <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center shadow-sm border border-slate-100 p-1 overflow-hidden">
    {{-- Perbaikan: Menggunakan asset() yang mengarah ke folder public/image --}}
                            <img src="{{ asset('logo.png') }}" 
                                alt="Logo CV Bima" 
                                class="w-full h-full object-contain"
                                onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'fas fa-leaf text-emerald-500 text-xl\'></i>';">
                        </div>
                        
                        <div class="leading-tight">
                            <span class="block font-bold text-sm text-slate-500 leading-none tracking-tight whitespace-nowrap">Bima Peraga Nusantara</span>
                            <span class="block text-[10px] text-slate-400 font-medium tracking-wider uppercase">Sistem Monitoring</span>
                        </div>
                    </div>
                </div>
                
                {{-- Navigasi Menu --}}
                <nav class="px-4 space-y-1.5">
                    
                    {{-- Dashboard --}}
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group
                       {{ request()->routeIs('dashboard') 
                           ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' 
                           : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
                        <i class="fas fa-th-large w-5 text-center {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-500' }}"></i> 
                        <span>Dashboard</span>
                    </a>

                    @if(auth()->user()->role == 'admin')
                        <div class="mt-6 mb-2 px-3">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Master Data</p>
                        </div>
                        
                        {{-- Manajemen Barang --}}
                        <a href="{{ route('barang.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group
                           {{ request()->routeIs('barang.*') ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
                            <i class="fas fa-box w-5 text-center {{ request()->routeIs('barang.*') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-500' }}"></i> 
                            <span>Data Barang</span>
                        </a>

                        {{-- Penjualan --}}
                        <a href="{{ route('barangkeluar.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group
                           {{ request()->routeIs('barangkeluar.*') ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
                            <i class="fas fa-shopping-basket w-5 text-center {{ request()->routeIs('barangkeluar.*') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-500' }}"></i> 
                            <span>Penjualan</span>
                        </a>
                    @endif

                    {{-- Stok Barang --}}
                    @if(in_array(auth()->user()->role, ['admin', 'gudang']))
                        <div class="mt-6 mb-2 px-3">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Inventory</p>
                        </div>
                        <a href="{{ route('stokbarang.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group
                           {{ request()->routeIs('stokbarang.*') ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
                            <i class="fas fa-clipboard-list w-5 text-center {{ request()->routeIs('stokbarang.*') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-500' }}"></i> 
                            <span>Stok Masuk</span>
                        </a>
                    @endif

                    @if(auth()->user()->role == 'admin')
                        <div class="mt-6 mb-2 px-3">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Analitik & Admin</p>
                        </div>

                        {{-- Akun --}}
                        <a href="{{ route('akunrole.index') }}" 
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group
                            {{ request()->routeIs('akunrole.*') ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
                             <i class="fas fa-user-shield w-5 text-center {{ request()->routeIs('akunrole.*') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-500' }}"></i> 
                             <span>Akun Pengguna</span>
                          </a>
                        
                        {{-- K-Means --}}
                        <a href="{{ route('k_means.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group
                           {{ request()->routeIs('k_means.*') ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
                            <i class="fas fa-shapes w-5 text-center {{ request()->routeIs('k_means.*') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-500' }}"></i> 
                            <span>Clustering (K-Means)</span>
                        </a>
                        
                        {{-- Apriori --}}
                        <a href="{{ route('apriori.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group
                           {{ request()->routeIs('apriori.*') ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
                            <i class="fas fa-project-diagram w-5 text-center {{ request()->routeIs('apriori.*') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-500' }}"></i> 
                            <span>Pola Beli (Apriori)</span>
                        </a>

                         <a href="{{ route('laporan.index') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 group
                           {{ request()->routeIs('laporan.*') ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' : 'text-slate-500 hover:bg-emerald-50 hover:text-emerald-600' }}">
                            <i class="fas fa-file-invoice-dollar w-5 text-center {{ request()->routeIs('laporan.*') ? 'text-white' : 'text-slate-400 group-hover:text-emerald-500' }}"></i> 
                            <span>Laporan</span>
                        </a>
                    @endif
                    
                </nav>
            </div>

            <div class="p-4 border-t border-slate-50">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl hover:bg-rose-50 transition-colors group">
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 group-hover:bg-rose-200 group-hover:text-rose-600 transition-colors">
                            <i class="fas fa-power-off text-xs"></i>
                        </div>
                        <div class="text-left">
                            <p class="text-xs font-bold text-slate-700 group-hover:text-rose-700">Keluar</p>
                            <p class="text-[10px] text-slate-400">Akhiri sesi</p>
                        </div>
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden bg-slate-50 relative">
            
            <header class="bg-white/80 backdrop-blur-md sticky top-0 z-10 border-b border-slate-100 flex justify-between items-center px-8 py-4">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="text-slate-400 hover:text-emerald-600 focus:outline-none lg:hidden">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-slate-800 tracking-tight">@yield('title', 'Dashboard')</h1>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-3 pl-4 border-l border-slate-100">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-slate-700">{{ Auth::user()->name ?? 'User' }}</p>
                            <p class="text-xs text-slate-400 capitalize">{{ Auth::user()->role ?? 'Staff' }}</p>
                        </div>
                        <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center border-2 border-white shadow-sm">
                             <i class="fas fa-user text-sm"></i>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto p-8">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>