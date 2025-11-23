@extends('layouts.app')

@section('title', 'Access Denied')

@section('content')
    <div class="max-w-xl mx-auto text-center py-12 space-y-4">
        <div class="inline-flex items-center justify-center rounded-full bg-rose-50 px-4 py-2 text-rose-600 font-semibold text-sm">
            403 - Forbidden
        </div>
        <h1 class="text-2xl font-semibold text-slate-900">
            Anda tidak memiliki izin untuk mengakses halaman ini.
        </h1>
        <p class="text-sm text-slate-600">
            Jika merasa ini kesalahan, hubungi administrator atau coba fitur lain yang tersedia untuk akun Anda.
        </p>
        <div class="pt-2">
            <a
                href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
            >
                Back to dashboard
            </a>
        </div>
    </div>
@endsection
