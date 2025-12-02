<x-guest-layout>
    @once
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @endonce

    {{-- Layout Utama: Split Screen --}}
    <div class="flex min-h-screen w-full bg-slate-50 overflow-hidden font-sans" style="font-family: 'Figtree', sans-serif;">
        
        {{-- BAGIAN KIRI: Gambar & Branding (Tidak berubah) --}}
        <div class="hidden lg:flex w-1/2 relative items-center justify-center overflow-hidden bg-gray-900">
            
            <img src="{{ asset('images/login-hero.png') }}" 
                 alt="Warehouse Background" 
                 class="absolute inset-0 w-full h-full object-cover opacity-40">
            
            {{-- Opacity dikurangi untuk mengurangi warna hijau --}}
            <div class="absolute inset-0 bg-gradient-to-t from-teal-900/60 via-teal-900/30 to-transparent"></div>

            {{-- Konten Branding --}}
            <div class="relative z-10 p-12 text-white max-w-lg">
                <div class="mb-6 bg-white/10 w-16 h-16 rounded-2xl flex items-center justify-center backdrop-blur-sm border border-white/20 shadow-lg">
                    <x-lucide-package class="w-8 h-8 text-white" />
                </div>
                
                <h2 class="text-4xl font-bold mb-4 tracking-tight leading-tight">
                    Optimalkan Operasional Gudang Anda.
                </h2>
                
                <p class="text-teal-50 text-lg leading-relaxed font-light">
                    Sistem manajemen gudang terintegrasi untuk tracking stok, pemenuhan pesanan, dan laporan real-time yang akurat.
                </p>
                
                {{-- Footer Kecil (Fitur Utama) --}}
                <div class="mt-10 pt-8 border-t border-white/10 flex gap-6 text-sm font-medium text-teal-200">
                    <div class="flex items-center gap-2">
                        <x-lucide-bar-chart-3 class="w-5 h-5" />
                        <span>Real-time Data</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-lucide-shield-check class="w-5 h-5" />
                        <span>Aman & Terpercaya</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- BAGIAN KANAN: Form Login --}}
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 relative">
            
            <div class="absolute inset-0 -z-10 h-full w-full bg-slate-50 bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] [background-size:20px_20px]"></div>

            <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-xl border border-slate-100">
                
                <div class="mb-8">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="bg-teal-600 p-2 rounded-lg lg:hidden">
                            <x-lucide-package class="w-6 h-6 text-white" />
                        </div>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Selamat Datang</h1>
                    </div>
                    <p class="text-slate-500 text-sm">Masuk ke dashboard Warenesia untuk melanjutkan.</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                {{-- PERUBAHAN: space-y-5 -> space-y-4 (Menghemat ruang vertikal) --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <div class="space-y-1.5">
                        <x-input-label for="email" value="Email" class="text-slate-700 font-medium" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <x-lucide-mail class="w-5 h-5" />
                            </div>
                            <x-text-input
                                id="email"
                                type="email"
                                name="email"
                                class="pl-10 block w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm py-2.5"
                                :value="old('email')"
                                required autofocus
                                autocomplete="username"
                                placeholder="nama@perusahaan.com"
                            />
                        </div>
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    <div class="space-y-1.5" x-data="{ show: false }">
                        <div class="flex items-center justify-between">
                            <x-input-label for="password" value="Password" class="text-slate-700 font-medium" />
                        </div>
                        
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <x-lucide-lock class="w-5 h-5" />
                            </div>
                            
                            {{-- Input Password --}}
                            <x-text-input
                                id="password"
                                class="pl-10 pr-10 block w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm py-2.5"
                                x-bind:type="show ? 'text' : 'password'"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                            />
                            
                            {{-- Tombol Toggle Mata --}}
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none transition-colors">
                                <x-lucide-eye x-show="!show" class="w-5 h-5" />
                                <x-lucide-eye-off x-show="show" x-cloak class="w-5 h-5" />
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-teal-600 shadow-sm focus:ring-teal-500 w-4 h-4">
                            <span class="select-none">Ingat saya</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="text-sm font-medium text-teal-600 hover:text-teal-700 transition-colors">
                                Lupa Password?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="w-full flex items-center justify-center gap-2 bg-teal-600 hover:bg-teal-700 text-white font-semibold py-3 px-4 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                        <span>Masuk</span>
                        <x-lucide-arrow-right class="w-4 h-4" />
                    </button>

                    {{-- PERUBAHAN: my-6 -> my-4 --}}
                    <div class="relative my-4">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-slate-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="bg-white px-2 text-slate-500">Atau</span>
                        </div>
                    </div>

                    <div class="text-center space-y-2">
                        <p class="text-sm text-slate-600">
                            Ingin menjadi mitra Supplier?
                            <a href="{{ route('supplier.register') }}" class="font-bold text-teal-600 hover:text-teal-700 hover:underline transition-colors">
                                Daftar Supplier
                            </a>
                        </p>
                    </div>
                </form>

                {{-- PERUBAHAN: mt-8 pt-6 -> mt-6 pt-4 --}}
                {{-- Footer Copyright --}}
                <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                    <p class="text-xs text-slate-400">
                        &copy; {{ date('Y') }} Warenesia WMS. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>