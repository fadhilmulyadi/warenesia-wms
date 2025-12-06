export function rackLocationField(initial) {
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
}

export function productForm(config) {
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
        errors: {},
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

        refreshSelect(inputName, options, value) {
            window.dispatchEvent(new CustomEvent('custom-select-update', {
                detail: {
                    name: inputName,
                    options: JSON.parse(JSON.stringify(options)),
                    value: value ? String(value) : ''
                }
            }));
        },

        openQuickCategory() {
            this.categoryQuick = { name: '', prefix: '' };
            this.errors = {};
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
                if (response.status === 422) {
                    this.errors = data.errors || {};
                } else {
                    alert(data.message || 'Gagal membuat kategori.');
                }
                return;
            }

            this.categoryOptions[data.id] = {
                label: data.name,
                image: data.image_path ?? null,
                prefix: data.sku_prefix
            };
            this.categoryPrefixes[data.id] = data.sku_prefix;
            this.form.category_id = String(data.id);
            this.refreshSelect('category_id', this.categoryOptions, this.form.category_id);
            this.updateSkuHint();
            this.$dispatch('close-modal', 'quick-category-modal');
            this.$dispatch('notify', { message: 'Kategori berhasil ditambahkan.', type: 'success' });
        },

        openQuickUnit() {
            this.unitQuick = { name: '' };
            this.errors = {};
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
                if (response.status === 422) {
                    this.errors = data.errors || {};
                } else {
                    alert(data.message || 'Gagal membuat satuan.');
                }
                return;
            }

            this.unitOptions[data.id] = data.name;
            this.form.unit_id = String(data.id);
            this.refreshSelect('unit_id', this.unitOptions, this.form.unit_id);
            this.$dispatch('close-modal', 'quick-unit-modal');
            this.$dispatch('notify', { message: 'Satuan berhasil ditambahkan.', type: 'success' });
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
}
