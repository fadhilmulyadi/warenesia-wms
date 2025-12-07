<x-guest-layout>
    <div class="min-h-screen w-full flex items-center justify-center p-6 relative overflow-hidden bg-slate-50">

        <div class="w-full max-w-xl bg-white border border-slate-100 rounded-2xl shadow-xl overflow-hidden">
            <div class="relative bg-teal-600 px-8 py-8 text-white">
                <div class="relative z-10 flex items-start gap-4">
                    <div
                        class="bg-white/15 border border-white/20 rounded-xl p-3 backdrop-blur-sm shadow-lg shadow-black/10">
                        <x-lucide-lock-keyhole class="w-7 h-7 text-white" />
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-teal-100">Keamanan Akun</p>
                        <h1 class="text-2xl font-semibold leading-tight">Reset Password</h1>
                        <p class="text-sm text-teal-50 mt-1">Pemulihan akun Warenesia melalui Administrator</p>
                    </div>
                </div>
            </div>

            <div class="px-8 py-8 space-y-6">
                <div class="space-y-3">
                    <p class="text-slate-700 leading-relaxed">
                        Demi keamanan data operasional, reset password mandiri sedang dinonaktifkan. Silakan hubungi
                        Administrator untuk mendapatkan kredensial baru atau memverifikasi kepemilikan akun Anda.
                    </p>

                    <div class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/70 px-4 py-3">
                        <div class="bg-teal-100 text-teal-700 p-2 rounded-lg">
                            <x-lucide-shield-check class="w-5 h-5" />
                        </div>
                        <div class="text-sm text-slate-600">
                            <p class="font-semibold text-slate-800">Standar keamanan Warenesia WMS</p>
                            <p class="mt-1 leading-relaxed">Setiap permintaan pemulihan diverifikasi untuk melindungi
                                akses stok, transaksi, dan data mitra.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-teal-100 bg-teal-50 p-4">
                    <div class="flex items-start gap-3">
                        <div class="bg-white p-2 rounded-lg shadow-sm border border-teal-100 text-teal-600">
                            <x-lucide-headset class="w-6 h-6" />
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-teal-700 font-semibold">IT
                                        Administrator</p>
                                    <p class="text-sm text-teal-900">Respons cepat via WhatsApp / Telegram</p>
                                </div>
                                <a href="https://wa.me/6281952671582?text=Halo%20Admin,%20saya%20lupa%20password%20akun%20WMS%20saya."
                                    target="_blank"
                                    class="inline-flex items-center gap-2 text-sm font-semibold text-teal-700 hover:text-teal-800 transition-colors">
                                    <span>+62 812-3456-7890</span>
                                    <x-lucide-arrow-up-right class="w-4 h-4" />
                                </a>
                            </div>

                            <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm text-teal-800">
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                                    Senin - Jumat, 08:00 - 17:00 WITA
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                                    Sertakan ID pengguna & perusahaan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-2">
                    <a href="{{ route('login') }}"
                        class="w-full inline-flex items-center justify-center gap-2 bg-white border border-slate-200 text-slate-700 font-semibold py-3 px-4 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-all duration-200 shadow-sm">
                        <x-lucide-arrow-left class="w-4 h-4" />
                        <span>Kembali ke Halaman Login</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>