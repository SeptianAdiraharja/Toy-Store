<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Serba 123 Toy Store') - Sistem Rekomendasi Apriori</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Panggilan Utama Aset Lokal Vite Offline -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex">

    <div id="sidebarBackdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 hidden md:hidden transition-opacity"></div>

    <aside id="sidebarNav" class="fixed inset-y-0 left-0 w-64 bg-slate-900 text-slate-300 flex flex-col z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out border-r border-slate-800 shadow-2xl">

        <!-- Brand Header -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800/80 bg-slate-950/40">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 via-indigo-600 to-purple-600 flex items-center justify-center text-white font-bold text-xl shadow-lg group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-shapes"></i>
                </div>
                <div>
                    <span class="font-black text-lg tracking-tight text-white block leading-tight">SERBA 123</span>
                    <span class="text-[10px] font-semibold text-slate-400 tracking-wider uppercase">Toy Store Jatinangor</span>
                </div>
            </a>
            <button type="button" onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white p-1 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Navigation Links (STRICT per role, sesuai struktur menu) -->
        <div class="flex-1 overflow-y-auto px-4 py-5 space-y-6 scrollbar-thin scrollbar-thumb-slate-800">

            <div>
                <nav class="space-y-1">

                </nav>
            </div>

            {{-- ================= MENU OWNER ================= --}}
            @if(auth()->user()->isOwner())
            <div>
                <nav class="space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-brand-600 to-indigo-600 text-white shadow-md shadow-brand-900/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie text-base {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                        <span>Dashboard</span>
                    </a>
                    <span class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Menu Owner</span>
                    <a href="{{ route('laporan.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('laporan.*') ? 'bg-amber-600 text-white shadow-md shadow-amber-900/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                        <i class="fa-solid fa-file-invoice text-base {{ request()->routeIs('laporan.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                        <span>Laporan Penjualan</span>
                    </a>

                    <a href="{{ route('apriori.hasil_rekomendasi') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('apriori.hasil_rekomendasi') ? 'bg-purple-600 text-white shadow-md shadow-purple-900/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                        <i class="fa-solid fa-lightbulb text-base {{ request()->routeIs('apriori.hasil_rekomendasi') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' }}"></i>
                        <span>Hasil Rekomendasi / Aturan Asosiasi</span>
                    </a>
                </nav>
            </div>
            @endif

            {{-- ================= MENU ADMIN ================= --}}
            @if(auth()->user()->isAdmin())
            <div>
                <nav class="space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-brand-600 to-indigo-600 text-white shadow-md shadow-brand-900/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                        <i class="fa-solid fa-chart-pie text-base {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                        <span>Dashboard</span>
                    </a>
                    <span class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider block my-6">Menu Admin</span>
                    <a href="{{ route('produks.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('produks.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-900/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                        <i class="fa-solid fa-boxes-stacked text-base {{ request()->routeIs('produks.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                        <span>Data Produk</span>
                    </a>

                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('users.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-900/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                        <i class="fa-solid fa-users text-base {{ request()->routeIs('users.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                        <span>Data User</span>
                    </a>

                    <a href="{{ route('transaksis.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('transaksis.*') ? 'bg-brand-600 text-white shadow-md shadow-brand-900/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                        <i class="fa-solid fa-receipt text-base {{ request()->routeIs('transaksis.*') ? 'text-white' : 'text-slate-400 group-hover:text-white' }}"></i>
                        <span>Data Transaksi</span>
                    </a>

                    <a href="{{ route('apriori.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('apriori.index') || request()->routeIs('apriori.process') ? 'bg-purple-600 text-white shadow-md shadow-purple-900/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                        <i class="fa-solid fa-brain text-base {{ request()->routeIs('apriori.index') || request()->routeIs('apriori.process') ? 'text-white' : 'text-purple-400 group-hover:text-purple-300' }}"></i>
                        <span>Proses Algoritma Apriori</span>
                    </a>

                    <a href="{{ route('apriori.hasil_rekomendasi') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('apriori.hasil_rekomendasi') ? 'bg-purple-600 text-white shadow-md shadow-purple-900/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                        <i class="fa-solid fa-lightbulb text-base {{ request()->routeIs('apriori.hasil_rekomendasi') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' }}"></i>
                        <span>Hasil Rekomendasi</span>
                    </a>
                </nav>
            </div>
            @endif

            {{-- ================= MENU KASIR ================= --}}
            @if(auth()->user()->isKasir())
            <div>
                <span class="px-3 text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Menu Kasir</span>
                <nav class="space-y-1">
                    <a href="{{ route('kasir.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('kasir.index') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                        <i class="fa-solid fa-cart-shopping text-base {{ request()->routeIs('kasir.index') ? 'text-white' : 'text-emerald-400 group-hover:text-emerald-300' }}"></i>
                        <span>Input Transaksi Penjualan</span>
                    </a>

                    <a href="{{ route('kasir.rekomendasi') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all group {{ request()->routeIs('kasir.rekomendasi') ? 'bg-purple-600 text-white shadow-md shadow-purple-900/30' : 'text-slate-300 hover:bg-slate-800/70 hover:text-white' }}">
                        <i class="fa-solid fa-lightbulb text-base {{ request()->routeIs('kasir.rekomendasi') ? 'text-white' : 'text-amber-400 group-hover:text-amber-300' }}"></i>
                        <span>Rekomendasi Produk (Real-time)</span>
                    </a>
                </nav>
            </div>
            @endif

        </div>

        <!-- User Profile Card & Logout (berlaku untuk semua role) -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/60">
            <div class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-slate-900 border border-slate-800">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-300 font-bold text-sm shrink-0">
                        <i class="fa-solid {{ auth()->user()->isOwner() ? 'fa-crown text-amber-400' : (auth()->user()->isAdmin() ? 'fa-user-gear text-blue-400' : 'fa-cash-register text-emerald-400') }}"></i>
                    </div>
                    <div class="overflow-hidden">
                        <span class="text-xs font-bold text-white block truncate">{{ auth()->user()->nama }}</span>
                        <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block truncate">
                            {{ auth()->user()->role }}
                        </span>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors cursor-pointer" title="Keluar / Logout">
                        <i class="fa-solid fa-right-from-bracket text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Layout Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 md:pl-64 min-h-screen">

        <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
            <div class="px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button type="button" onclick="toggleSidebar()" class="md:hidden p-2 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100 transition-colors">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 tracking-tight">@yield('title', 'Serba 123')</h2>
                        <p class="text-xs text-slate-500 hidden sm:block">Sistem Informasi Penjualan & Market Basket Analysis (Apriori)</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 border border-slate-200 text-slate-700 rounded-full text-xs font-semibold">
                        <i class="fa-regular fa-clock text-slate-400"></i>
                        <span>{{ date('d M Y') }}</span>
                    </span>

                    {{-- Tombol cepat Kasir POS hanya muncul untuk role Kasir --}}
                    @if(auth()->user()->isKasir())
                    <a href="{{ route('kasir.index') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-2">
                        <i class="fa-solid fa-cash-register"></i> Kasir POS
                    </a>
                    @endif
                </div>
            </div>
        </header>

        <main class="flex-grow p-4 sm:p-6 lg:p-8">

            @if (session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 p-1">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                    <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 p-1">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="bg-white border-t border-slate-200 py-4 px-6 text-center sm:text-left text-xs text-slate-500 mt-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <p>&copy; {{ date('Y') }} <strong>Serba 123 Toy Store</strong> - Jatinangor</p>
            <p class="text-slate-400">Skripsi oleh Faisal Ahmad Mubaroq (22110145) - STMIK Mardira Indonesia</p>
        </footer>

    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarNav');
            const backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar && backdrop) {
                sidebar.classList.toggle('-translate-x-full');
                backdrop.classList.toggle('hidden');
            }
        }
    </script>
    @stack('scripts')
</body>
</html>