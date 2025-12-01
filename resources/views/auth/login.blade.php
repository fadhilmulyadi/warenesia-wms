<x-guest-layout>

    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">
        
        <div class="text-center mb-6">
            <p class="text-3xl font-extrabold text-teal-600 mt-1">Warenesia</p>
            <p class="text-sm text-slate-500 mt-1">Silakan masuk ke sistem</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email -->
            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input
                    id="email"
                    type="email"
                    name="email"
                    class="mt-1 block w-full"
                    :value="old('email')"
                    required autofocus
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

            <!-- Remember -->
            <div class="flex items-center justify-between mt-4">
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-teal-600 shadow-sm">
                    Ingat Saya
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-sm text-teal-600 hover:text-teal-700">
                        Lupa password?
                    </a>
                @endif
            </div>

            <!-- Login Button -->
            <div class="mt-6">
                <x-primary-button class="w-full justify-center">
                    Masuk
                </x-primary-button>
            </div>

            <!-- Link Register -->
            <div class="mt-4 text-center">
                <span class="text-sm text-slate-600">Belum punya akun?</span>
                <a href="{{ route('register') }}"
                   class="text-sm text-teal-600 hover:text-teal-700 font-semibold">
                    Daftar di sini
                </a>
            </div>
        </form>

    </div>

</x-guest-layout>