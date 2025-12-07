<x-guest-layout>
    {{-- Tambahkan Wrapper ini agar konten kembali ke tengah layar dan mendapat background --}}
    <div class="min-h-screen flex flex-col items-center justify-center p-6 bg-slate-50 relative">

        <div
            class="absolute inset-0 -z-10 h-full w-full bg-slate-50 bg-[radial-gradient(#e2e8f0_1px,transparent_1px)] [background-size:20px_20px]">
        </div>

        <div class="w-full max-w-2xl bg-white border border-slate-200 rounded-2xl shadow-lg p-6 sm:p-8 space-y-6 mx-4 sm:mx-auto relative z-10"
            style="font-family: 'Figtree', sans-serif;">

            <div class="text-center space-y-1">
                <div class="flex flex-col items-center justify-center mb-4">
                    <p class="text-3xl font-extrabold text-teal-600">Warenesia</p>
                </div>

                <h1 class="text-xl font-semibold text-slate-900">Pendaftaran Mitra Supplier</h1>
                <p class="hidden md:block text-sm text-slate-500">Daftarkan perusahaan Anda dan tunggu persetujuan
                    admin.</p>
            </div>

            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('supplier.register') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="space-y-1">
                        <x-input-label for="company_name" value="Nama Perusahaan" />
                        <x-text-input id="company_name"
                            class="block w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm py-2.5"
                            type="text" name="company_name" :value="old('company_name')" required autofocus
                            placeholder="PT. Contoh Sejahtera" />
                        <x-input-error :messages="$errors->get('company_name')" />
                    </div>

                    <div class="space-y-1">
                        <x-input-label for="supplier_name" value="Nama Kontak (PIC)" />
                        <x-text-input id="supplier_name"
                            class="block w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm py-2.5"
                            type="text" name="supplier_name" :value="old('supplier_name')" required
                            placeholder="Budi Santoso" />
                        <x-input-error :messages="$errors->get('supplier_name')" />
                    </div>

                    <div class="space-y-1">
                        <x-input-label for="email" value="Email Perusahaan" />
                        <x-text-input id="email"
                            class="block w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm py-2.5"
                            type="email" name="email" :value="old('email')" required
                            placeholder="info@perusahaan.com" />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>

                    <div class="space-y-1">
                        <x-input-label for="phone" value="Nomor Telepon / WA" />
                        <x-text-input id="phone"
                            class="block w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm py-2.5"
                            type="tel" name="phone" :value="old('phone')" required placeholder="081234567890"
                            inputmode="numeric" pattern="[0-9]*"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                        <x-input-error :messages="$errors->get('phone')" />
                    </div>

                    {{-- Password (Dengan Toggle Show/Hide) --}}
                    <div class="space-y-1" x-data="{ show: false }">
                        <x-input-label for="password" value="Password" />
                        <div class="relative">
                            <x-text-input id="password"
                                class="block w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm pr-10 py-2.5"
                                x-bind:type="show ? 'text' : 'password'" name="password" required
                                autocomplete="new-password" />
                            <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none">
                                <x-lucide-eye x-show="!show" class="w-5 h-5" />
                                <x-lucide-eye-off x-show="show" x-cloak class="w-5 h-5" />
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" />
                    </div>

                    {{-- Konfirmasi Password (Dengan Toggle Show/Hide) --}}
                    <div class="space-y-1" x-data="{ show: false }">
                        <x-input-label for="password_confirmation" value="Konfirmasi Password" />
                        <div class="relative">
                            <x-text-input id="password_confirmation"
                                class="block w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm pr-10 py-2.5"
                                x-bind:type="show ? 'text' : 'password'" name="password_confirmation" required
                                autocomplete="new-password" />
                            <button type="button" @click="show = !show"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 focus:outline-none">
                                <x-lucide-eye x-show="!show" class="w-5 h-5" />
                                <x-lucide-eye-off x-show="show" x-cloak class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex flex-col gap-4">
                    <div class="bg-slate-50 p-3 rounded-lg flex items-start gap-2">
                        <x-lucide-info class="w-5 h-5 text-slate-400 mt-0.5 shrink-0" />
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Akun Anda akan berstatus <strong>Pending</strong> setelah pendaftaran. Anda baru dapat
                            melakukan stok barang setelah disetujui oleh Admin Gudang.
                        </p>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-center gap-3 justify-end">
                        <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center h-10 px-5 rounded-lg 
                                  border border-slate-300 bg-white text-sm font-semibold text-slate-700
                                  hover:bg-slate-50 hover:border-slate-400 transition-colors">
                            Kembali ke Login
                        </a>

                        <x-primary-button
                            class="w-full sm:w-auto justify-center h-10 px-6 bg-teal-600 hover:bg-teal-700">
                            Daftar Sekarang
                        </x-primary-button>
                    </div>
                </div>

            </form>
        </div>

    </div>
</x-guest-layout>
