@php
    $target = $product ?? new \App\Models\Product();
    $isEdit = $target->exists;
    
    $alpineData = [
        'autoSku' => !$isEdit,
        'imagePreview' => $target->image_path,
    ];
@endphp

{{-- Error Summary --}}
@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-red-100 bg-red-50 p-4">
        <div class="flex items-start gap-3">
            <x-lucide-alert-triangle class="h-5 w-5 text-red-600 shrink-0" />
            <div>
                <h3 class="text-sm font-bold text-red-800">Mohon periksa kembali inputan Anda</h3>
                <ul class="mt-1 list-disc list-inside text-xs text-red-700 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<div x-data="{ 
    autoSku: {{ $isEdit ? 'false' : 'true' }},
    imagePreview: @js($alpineData['imagePreview']),
    
    handleImage(event) {
        const file = event.target.files[0];
        if (file) {
            this.imagePreview = URL.createObjectURL(file);
        }
    }
}" class="pb-20 space-y-6">

    {{-- 
        Layout Grid 
        items-start: Sidebar tinggi sesuai kontennya sendiri.
        items-stretch: Sidebar dipaksa sama tinggi dengan konten utama (Form).
        Kita gunakan items-stretch agar sidebar.blade.php yang pakai h-full bisa rata bawah.
    --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
        
        {{-- KOLOM KIRI --}}
        <div class="lg:col-span-2 flex flex-col gap-6">
            {{-- Informasi Dasar & Harga --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm divide-y divide-slate-100 overflow-hidden h-full">
                @include('products.partials.form-sections._general', ['product' => $target])
                @include('products.partials.form-sections._pricing', ['product' => $target])
                @include('products.partials.form-sections._inventory', ['product' => $target])
            </div>
        </div>

        {{-- KOLOM KANAN --}}
        <div class="flex flex-col gap-6 h-full">
            <div class="shrink-0">
                @include('products.partials.form-sections._media', ['product' => $target])
            </div>
            
            <div class="flex-1 min-h-0">
                @include('products.partials.form-sections._sidebar', ['product' => $target])
            </div>
        </div>

    </div>
</div>