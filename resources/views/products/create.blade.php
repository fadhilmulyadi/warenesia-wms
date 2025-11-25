@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('page-header')
    <div class="flex flex-col">
        <h1 class="text-base font-semibold text-slate-900">Tambah Produk</h1>
        <p class="text-xs text-slate-500">
            Daftarkan produk baru ke gudang Warenesia.
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('products.index') }}"
           class="inline-flex items-center rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-700 hover:bg-slate-50">
            Back to list
        </a>

        <button
            type="submit"
            form="product-form"
            class="inline-flex items-center rounded-lg bg-teal-500 px-4 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-teal-600">
            Save
        </button>
    </div>
@endsection

@section('content')
    {{-- 
        x-data didefinisikan di wrapper utama agar bisa diakses 
        baik oleh tombol di dalam '_tabs' maupun modal di bawah.
    --}}
    <div class="max-w-6xl mx-auto space-y-4"
         x-data="{ 
            isCategoryModalOpen: false,
            newCategoryName: '',
            newCategoryDesc: '',
            isLoading: false,

            async saveCategory() {
                if (!this.newCategoryName) {
                    Swal.fire('Validasi', 'Nama kategori wajib diisi', 'warning');
                    return;
                }

                this.isLoading = true;

                try {
                    // Gunakan route() Laravel di sini
                    const response = await axios.post('{{ route('categories.quick-store') }}', {
                        name: this.newCategoryName,
                        description: this.newCategoryDesc
                    });

                    if (response.data && response.data.id) {
                        // 1. Tambahkan Option Baru ke Select (DOM Manipulation)
                        const selectElement = document.getElementById('category_select');
                        if(selectElement) {
                            const newOption = new Option(response.data.name, response.data.id, true, true);
                            selectElement.add(newOption, undefined);
                            // Trigger event change agar UI framework lain (jika ada) sadar perubahan
                            selectElement.dispatchEvent(new Event('change'));
                        }

                        // 2. Reset & Tutup Modal
                        this.newCategoryName = '';
                        this.newCategoryDesc = '';
                        this.isCategoryModalOpen = false;

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Kategori berhasil dibuat & dipilih otomatis!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } catch (error) {
                    console.error(error);
                    const msg = error.response?.data?.message || 'Terjadi kesalahan saat menyimpan kategori.';
                    Swal.fire('Gagal', msg, 'error');
                } finally {
                    this.isLoading = false;
                }
            }
         }"
    >
        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
                <div class="font-semibold mb-1">Terjadi kesalahan:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $errorMessage)
                        <li>{{ $errorMessage }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- FORM UTAMA PRODUK --}}
        <form
            id="product-form"
            method="POST"
            action="{{ route('products.store') }}"
        >
            @csrf

            {{-- 
                Pastikan di dalam _tabs.blade.php, 
                <select> kategori memiliki id="category_select" 
            --}}
            @include('products._tabs', [
                'mode'       => 'create',
                'product'    => null,
                'categories' => $categories,
                'suppliers'  => $suppliers,
            ])
        </form>

        {{-- MODAL KATEGORI (Berada DI LUAR Form Utama) --}}
        @php
            $canManageCategories = auth()->user()?->can('create', \App\Models\Category::class);
        @endphp

        @if($canManageCategories)
            <div
                x-cloak
                x-show="isCategoryModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
                x-transition.opacity
            >
                <div 
                    class="bg-white rounded-2xl shadow-xl w-full max-w-md p-5 text-xs"
                    @click.away="isCategoryModalOpen = false"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-sm font-semibold text-slate-900">Quick Add Category</h2>
                            <p class="text-[11px] text-slate-500">Tambah kategori tanpa refresh halaman.</p>
                        </div>
                        <button
                            type="button"
                            class="text-slate-400 hover:text-slate-600"
                            @click="isCategoryModalOpen = false"
                        >✕</button>
                    </div>

                    {{-- Form ini tidak memiliki tag <form> agar tidak memicu submit browser --}}
                    <div class="space-y-3">
                        <div>
                            <label class="text-[11px] text-slate-600 mb-1 block">Category Name *</label>
                            <input
                                type="text"
                                x-model="newCategoryName"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px] focus:border-teal-500 focus:ring-teal-500"
                                placeholder="Contoh: Elektronik"
                            >
                        </div>

                        <div>
                            <label class="text-[11px] text-slate-600 mb-1 block">Description</label>
                            <textarea
                                x-model="newCategoryDesc"
                                rows="2"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px] focus:border-teal-500 focus:ring-teal-500"
                                placeholder="Opsional"
                            ></textarea>
                        </div>

                        <div class="flex justify-end gap-2 pt-2">
                            <button
                                type="button"
                                class="px-3 py-1.5 rounded-lg text-[11px] border border-slate-200 hover:bg-slate-50"
                                @click="isCategoryModalOpen = false"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-teal-500 text-white hover:bg-teal-600 flex items-center gap-2"
                                @click="saveCategory()"
                                :disabled="isLoading"
                            >
                                <span x-show="isLoading" class="animate-spin h-3 w-3 border-2 border-white border-t-transparent rounded-full"></span>
                                <span x-text="isLoading ? 'Saving...' : 'Save Category'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection