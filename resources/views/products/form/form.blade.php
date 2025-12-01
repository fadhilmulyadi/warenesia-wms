@php
    /** @var \App\Models\Product $product */
    $readonly = $readonly ?? false; 
    $target = $product ?? new \App\Models\Product();
    $categories = $categories ?? collect();
    $suppliers = $suppliers ?? collect();
    $units = $units ?? collect();

    $categoryOptions = $categoryOptions ?? $categories->pluck('name', 'id')->toArray();

    $supplierOptions = $supplierOptions ?? $suppliers->mapWithKeys(function ($item) {
        return [$item->id => $item->name . ($item->contact_person ? ' (' . $item->contact_person . ')' : '')];
    })->toArray();

    $unitOptions = $unitOptions ?? $units->pluck('name', 'id')->toArray();

    $categoryPrefixes = $categoryPrefixes ?? $categories->pluck('sku_prefix', 'id')->filter()->toArray();
    $initialSku = old('sku', $target->sku);
    $initialSkuNumber = is_string($initialSku)
        ? preg_replace('/^[^-]*-/', '', $initialSku)
        : '';
    $imageUrl = $target->image_path
        ? \Illuminate\Support\Facades\Storage::url($target->image_path)
        : null;
@endphp

<div x-data="productForm({
        readonly: @js($readonly),
        initialSku: @js($initialSku),
        initialSkuNumber: @js($initialSkuNumber),
        initialCategory: @js(old('category_id', $target->category_id)),
        initialUnit: @js(old('unit_id', $target->unit_id)),
        categories: @js($categoryOptions),
        units: @js($unitOptions),
        categoryPrefixes: @js($categoryPrefixes),
        categoryEndpoint: @js(route('categories.quick-store')),
        unitEndpoint: @js(route('units.quick-store')),
        csrf: @js(csrf_token()),
        initialImage: @js($imageUrl),
    })"
    class="pb-20 space-y-6"
>
    @if (!$readonly && $errors->any())
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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-stretch">
        
        {{-- KOLOM KIRI --}}
        <div class="lg:col-span-2 flex flex-col gap-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm divide-y divide-slate-100 overflow-hidden h-full">
                
                @include('products.form.general', ['product' => $target, 'readonly' => $readonly])
                @include('products.form.pricing', ['product' => $target, 'readonly' => $readonly])
                @include('products.form.inventory', ['product' => $target, 'readonly' => $readonly])

            </div>
        </div>

        {{-- KOLOM KANAN --}}
        <div class="flex flex-col gap-6 h-full">
            <div class="shrink-0">
                @include('products.form.media', ['product' => $target, 'readonly' => $readonly])
            </div>
            
            <div class="flex-1 min-h-0">
                @include('products.form.sidebar', [
                    'product' => $target,
                    'readonly' => $readonly,
                    'categoryOptions' => $categoryOptions,
                    'supplierOptions' => $supplierOptions,
                    'unitOptions' => $unitOptions,
                ])
            </div>
        </div>

    </div>

    {{-- Quick Add Category Modal --}}
    <x-modal name="quick-category-modal" maxWidth="lg">
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900">Tambah Kategori Cepat</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600" x-on:click="$dispatch('close-modal', 'quick-category-modal')">
                    <x-lucide-x class="w-5 h-5" />
                </button>
            </div>

            <div class="space-y-4">
                <div class="space-y-2">
                    <x-input-label value="Nama Kategori" class="text-sm font-semibold text-slate-700" />
                    <input type="text" x-model="categoryQuick.name" x-on:input="syncCategoryPrefix()" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Contoh: Peralatan Dapur">
                </div>
                <div class="space-y-2">
                    <x-input-label value="SKU Prefix" class="text-sm font-semibold text-slate-700" />
                    <input type="text" x-model="categoryQuick.prefix" readonly class="w-full rounded-xl border-slate-200 text-sm shadow-sm bg-slate-50 tracking-[0.2em] font-semibold uppercase">
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-action-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'quick-category-modal')">Batal</x-action-button>
                <x-action-button type="button" variant="primary" icon="save" x-on:click="saveQuickCategory()">Simpan</x-action-button>
            </div>
        </div>
    </x-modal>

    {{-- Quick Add Unit Modal --}}
    <x-modal name="quick-unit-modal" maxWidth="md">
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-900">Tambah Satuan Cepat</h3>
                <button type="button" class="text-slate-400 hover:text-slate-600" x-on:click="$dispatch('close-modal', 'quick-unit-modal')">
                    <x-lucide-x class="w-5 h-5" />
                </button>
            </div>

            <div class="space-y-2">
                <x-input-label value="Nama Satuan" class="text-sm font-semibold text-slate-700" />
                <input type="text" x-model="unitQuick.name" class="w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500" placeholder="Contoh: Box, Pcs, Liter">
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <x-action-button type="button" variant="secondary" x-on:click="$dispatch('close-modal', 'quick-unit-modal')">Batal</x-action-button>
                <x-action-button type="button" variant="primary" icon="save" x-on:click="saveQuickUnit()">Simpan</x-action-button>
            </div>
        </div>
    </x-modal>
</div>

@push('scripts')
    @once
        <script>
            const registerProductForm = () => {
                window.rackLocationField = function (initial) {
                    return {
                        value: initial || '',
                        format() {
                            if (!this.value) {
                                return;
                            }

                            let raw = this.value
                                .toUpperCase()
                                .replace(/[^A-Z0-9]/g, '');

                            if (!raw.length) {
                                this.value = '';
                                return;
                            }

                            const zone = raw[0];
                            const rest = raw.slice(1);

                            if (!/[A-Z]/.test(zone)) {
                                this.value = raw;
                                return;
                            }

                            const rack = rest.slice(0, 2);
                            const bin = rest.slice(2, 4);

                            let out = zone;

                            if (rack.length > 0) {
                                out += rack.replace(/\D/g, '');
                            }

                            if (rack.length === 2) {
                                out += '-';
                            }

                            if (bin.length > 0) {
                                out += bin.replace(/\D/g, '');
                            }

                            this.value = out;
                        },
                    };
                };

                window.productForm = function (config) {
                    return {
                        readonly: config.readonly,
                        form: {
                            sku: config.initialSku || '',
                            category_id: config.initialCategory ? String(config.initialCategory) : '',
                            unit_id: config.initialUnit ? String(config.initialUnit) : '',
                        },
                        categoryOptions: { ...(config.categories || {}) },
                        unitOptions: { ...(config.units || {}) },
                        categoryPrefixes: { ...(config.categoryPrefixes || {}) },
                        skuNumber: config.initialSkuNumber || '',
                        categoryQuick: { name: '', prefix: '' },
                        unitQuick: { name: '' },
                        skuHint: '',
                        imagePreview: config.initialImage || null,
                        init() {
                            if (!this.skuNumber && this.form.sku) {
                                this.skuNumber = String(this.form.sku).replace(/^[^-]*-/, '');
                            }
                            this.updateSkuHint();
                        },
                        updateSkuHint() {
                            const prefix = this.categoryPrefixes?.[this.form.category_id] ?? '';
                            const number = (this.skuNumber || '').toString();
                            const parts = [prefix, number].filter(Boolean);
                            this.form.sku = parts.join('-');
                            this.skuHint = this.form.sku || (prefix ? `${prefix}-XXXX` : 'SKU akan dibuat otomatis');
                        },
                        refreshSelect(refName, options, value) {
                            const el = this.$refs[refName];
                            if (el && el.__x) {
                                el.__x.$data.options = options;
                                el.__x.$data.value = value ? String(value) : '';

                                const option = value ? (options?.[value] ?? null) : null;
                                let label = '';
                                
                                if (option) {
                                        if (typeof option === 'object' && option !== null) {
                                            label = option.label ?? '';
                                        } else {
                                            label = String(option);
                                        }
                                    }

                                    el.__x.$data.search = label;
                            }
                        },
                        openQuickCategory() {
                            this.categoryQuick = { name: '', prefix: '' };
                            this.$dispatch('open-modal', 'quick-category-modal');
                        },
                        generatePrefix(name) {
                            const slug = (name || '')
                                .normalize('NFD')
                                .replace(/[\u0300-\u036f]/g, '')
                                .toLowerCase()
                                .replace(/[^a-z0-9\s-]/g, '')
                                .trim()
                                .replace(/\s+/g, '-');

                            if (!slug) return '';

                            const words = slug.split('-').filter(Boolean);
                            let draft = '';

                            for (const word of words) {
                                draft += word.charAt(0);
                                if (draft.length >= 3) break;
                            }

                            if (draft.length < 3) {
                                const tail = words[words.length - 1].slice(1);
                                draft += tail.slice(0, Math.max(0, 3 - draft.length));
                            }

                            return draft.slice(0, 3).toUpperCase();
                        },
                        syncCategoryPrefix() {
                            this.categoryQuick.prefix = this.generatePrefix(this.categoryQuick.name);
                        },
                        async saveQuickCategory() {
                            if (!this.categoryQuick.name.trim()) {
                                return;
                            }

                            this.syncCategoryPrefix();

                            const payload = new FormData();
                            payload.append('name', this.categoryQuick.name);
                            payload.append('sku_prefix', this.categoryQuick.prefix);

                            const response = await fetch(config.categoryEndpoint, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': config.csrf,
                                    'Accept': 'application/json',
                                },
                                body: payload,
                            });

                            let data = {};
                            try {
                                data = await response.json();
                            } catch (e) {
                                data = {};
                            }

                            if (!response.ok) {
                                alert(data.message || 'Gagal membuat kategori.');
                                return;
                            }

                            this.categoryOptions[data.id] = {
                                label: `${data.name} (${data.sku_prefix})`,
                                image: data.image_path ?? null,
                                prefix: data.sku_prefix
                            };
                            this.categoryPrefixes[data.id] = data.sku_prefix;
                            this.form.category_id = String(data.id);
                            this.refreshSelect('categorySelect', this.categoryOptions, this.form.category_id);
                            this.updateSkuHint();
                            this.$dispatch('close-modal', 'quick-category-modal');
                        },
                        openQuickUnit() {
                            this.unitQuick = { name: '' };
                            this.$dispatch('open-modal', 'quick-unit-modal');
                        },
                        async saveQuickUnit() {
                            if (!this.unitQuick.name.trim()) {
                                return;
                            }

                            const payload = new FormData();
                            payload.append('name', this.unitQuick.name);

                            const response = await fetch(config.unitEndpoint, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': config.csrf,
                                    'Accept': 'application/json',
                                },
                                body: payload,
                            });

                            let data = {};
                            try {
                                data = await response.json();
                            } catch (e) {
                                data = {};
                            }

                            if (!response.ok) {
                                alert(data.message || 'Gagal membuat satuan.');
                                return;
                            }

                            this.unitOptions[data.id] = data.name;
                            this.form.unit_id = String(data.id);
                            this.refreshSelect('unitSelect', this.unitOptions, this.form.unit_id);
                            this.$dispatch('close-modal', 'quick-unit-modal');
                        },
                        handleImage(event) {
                            if (this.readonly) {
                                return;
                            }

                            const file = event.target.files[0];
                            if (file) {
                                this.imagePreview = URL.createObjectURL(file);
                            }
                        },
                    };
                };
            };

            if (window.Alpine) {
                registerProductForm();
            } else {
                document.addEventListener('alpine:init', registerProductForm);
            }
        </script>
    @endonce
@endpush
