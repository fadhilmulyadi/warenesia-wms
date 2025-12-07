<x-guest-layout>

    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">

        <div class="text-center mb-6">
            <p class="text-sm text-slate-500 mt-1">Buat akun baru untuk masuk</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div>
                <x-input-label for="name" value="Nama" />
                <x-text-input
                    id="name"
                    type="text"
                    name="name"
                    class="mt-1 block w-full"
                    :value="old('name')"
                    required autofocus
                />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email -->
            <div class="mt-4">
                <x-input-label for="email" value="Email" />
                <x-text-input
                    id="email"
                    type="email"
                    name="email"
                    class="mt-1 block w-full"
                    :value="old('email')"
                    required
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-input-label for="password" value="Password" />
                <x-text-input
                    id="password"
                    type="password"
                    name="password"
                    class="mt-1 block w-full"
                    required
                />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" value="Konfirmasi Password" />
                <x-text-input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="mt-1 block w-full"
                    required
                />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <!-- Register Button -->
            <div class="mt-6">
                <x-primary-button class="w-full justify-center">
                    Daftar
                </x-primary-button>
            </div>

            <!-- Login Link -->
            <div class="mt-4 text-center">
                <span class="text-sm text-slate-600">Sudah punya akun?</span>
                <a href="{{ route('login') }}"
                   class="text-sm text-teal-600 hover:text-teal-700 font-semibold">
                    Masuk di sini
                </a>
            </div>
        </form>

    </div>

</x-guest-layout>