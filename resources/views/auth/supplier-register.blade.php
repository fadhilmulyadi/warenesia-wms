<x-guest-layout>
    <div class="max-w-xl mx-auto bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8 space-y-5">
        <div class="space-y-1 text-center">
            <h1 class="text-lg font-semibold text-slate-900">Pendaftaran Supplier</h1>
            <p class="text-xs text-slate-500">Daftarkan perusahaan Anda dan tunggu approval admin.</p>
        </div>

        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('supplier.register') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <x-input-label for="company_name" value="Nama Perusahaan" />
                    <x-text-input id="company_name" class="block w-full" type="text" name="company_name" :value="old('company_name')" required autofocus />
                    <x-input-error :messages="$errors->get('company_name')" />
                </div>

                <div class="space-y-1">
                    <x-input-label for="supplier_name" value="Nama Kontak" />
                    <x-text-input id="supplier_name" class="block w-full" type="text" name="supplier_name" :value="old('supplier_name')" required />
                    <x-input-error :messages="$errors->get('supplier_name')" />
                </div>

                <div class="space-y-1">
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" required />
                    <x-input-error :messages="$errors->get('email')" />
                </div>

                <div class="space-y-1">
                    <x-input-label for="phone" value="Nomor Telepon" />
                    <x-text-input id="phone" class="block w-full" type="text" name="phone" :value="old('phone')" required />
                    <x-input-error :messages="$errors->get('phone')" />
                </div>

                <div class="space-y-1 md:col-span-2">
                    <x-input-label for="category_of_goods" value="Kategori Barang (Opsional)" />
                    <x-text-input id="category_of_goods" class="block w-full" type="text" name="category_of_goods" :value="old('category_of_goods')" />
                    <x-input-error :messages="$errors->get('category_of_goods')" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <x-input-label for="password" value="Password" />
                    <x-text-input id="password" class="block w-full" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" />
                </div>

                <div class="space-y-1">
                    <x-input-label for="password_confirmation" value="Konfirmasi Password" />
                    <x-text-input id="password_confirmation" class="block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                <p class="text-xs text-slate-500">
                    Dengan mendaftar, akun Anda akan berstatus pending sampai disetujui admin.
                </p>
                <div class="flex items-center gap-2">
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-teal-700 hover:text-teal-800">Kembali ke login</a>
                    <x-primary-button>
                        Daftar
                    </x-primary-button>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>
