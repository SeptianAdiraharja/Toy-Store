<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Serba 123 Toy Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Baloo+2:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['"Baloo 2"', 'sans-serif'],
                    },
                    colors: {
                        toy: {
                            pink: '#f43f5e',
                            amber: '#f59e0b',
                            emerald: '#10b981',
                            indigo: '#6366f1',
                            purple: '#8b5cf6',
                            sky: '#0ea5e9',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .bg-dots {
            background-image: radial-gradient(rgba(255,255,255,0.08) 1.5px, transparent 1.5px);
            background-size: 22px 22px;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-14px) rotate(3deg); }
        }
        .float-anim { animation: float 5s ease-in-out infinite; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-950 via-slate-900 to-purple-950 bg-dots text-slate-100 font-sans antialiased min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Decorative blurred blobs -->
    <div class="absolute -top-20 -left-20 w-72 h-72 bg-toy-pink/20 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-24 -right-16 w-80 h-80 bg-toy-sky/20 rounded-full blur-3xl"></div>
    <div class="absolute top-1/3 right-10 w-40 h-40 bg-toy-amber/10 rounded-full blur-3xl"></div>

    <div class="w-full max-w-4xl grid md:grid-cols-2 gap-8 items-center relative z-10">

        <!-- Left side: Illustration Image -->
        <div class="hidden md:flex flex-col items-center justify-center text-center">
            <div class="float-anim">
                <img src="https://ih1.redbubble.net/image.5822301973.7736/st,small,507x507-pad,600x600,f8f8f8.jpg"
                     alt="Toy Store Illustration"
                     class="w-64 h-64 object-contain rounded-3xl shadow-2xl ring-4 ring-white/10 bg-white/5 p-3">
            </div>
            <h2 class="mt-6 font-display text-2xl font-bold text-white">Belanja Mainan Jadi Lebih Seru!</h2>
            <p class="text-sm text-slate-400 mt-2 max-w-xs">Sistem rekomendasi cerdas berbasis Algoritma Apriori untuk Serba 123 Toy Store.</p>
        </div>

        <!-- Right side: Login Card -->
        <div class="w-full max-w-md mx-auto">
            <!-- Logo Header -->
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-toy-pink via-toy-purple to-toy-indigo flex items-center justify-center text-white text-3xl font-extrabold shadow-xl shadow-purple-900/40 mx-auto mb-4">
                    <i class="fa-solid fa-shapes"></i>
                </div>
                <h1 class="font-display text-2xl font-extrabold tracking-tight text-white">SERBA 123 TOY STORE</h1>
                <p class="text-sm text-slate-400 mt-1">Sistem Rekomendasi Produk Metode Algoritma Apriori</p>
            </div>

            <!-- Card Container -->
            <div class="bg-slate-800/70 backdrop-blur-xl border border-slate-700/60 rounded-3xl p-6 sm:p-8 shadow-2xl">
                <h2 class="text-lg font-bold text-slate-100 mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-right-to-bracket text-toy-sky"></i> Masuk ke Sistem
                </h2>

                @if ($errors->any())
                    <div class="mb-5 p-3.5 bg-rose-500/10 border border-rose-500/30 text-rose-300 rounded-xl text-sm flex items-start gap-2.5">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="username" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Username</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                <i class="fa-solid fa-user text-sm"></i>
                            </span>
                            <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-toy-sky focus:border-transparent placeholder-slate-500"
                                placeholder="Masukkan username Anda">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500">
                                <i class="fa-solid fa-lock text-sm"></i>
                            </span>
                            <input type="password" id="password" name="password" required
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-900/80 border border-slate-700 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-toy-sky focus:border-transparent placeholder-slate-500"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-slate-700 bg-slate-900 text-toy-sky focus:ring-toy-sky">
                            Ingat Saya
                        </label>
                    </div>

                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-toy-pink via-toy-purple to-toy-indigo hover:brightness-110 text-white font-bold text-sm rounded-xl shadow-lg shadow-purple-900/40 transition-all cursor-pointer">
                        Masuk Sekarang <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
                    </button>
                </form>

            </div>

            <p class="text-center text-xs text-slate-500 mt-6">
                Serba 123 Toy Store Jatinangor &copy; {{ date('Y') }}
            </p>
        </div>
    </div>

    <script>
        function quickLogin(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
            document.querySelector('form').submit();
        }
    </script>
</body>
</html>