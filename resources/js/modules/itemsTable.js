export function submitFormWithValidation(formId) {
    const form = document.getElementById(formId);

    if (!form) return;

    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
        return;
    }

    const submitEvent = new Event('submit', { cancelable: true, bubbles: true });

    if (form.dispatchEvent(submitEvent)) {
        form.submit();
    }
}

export function itemsTable(config) {
    const {
        initialItems = [],
        productStocks = {},
        productSkus = {},
        purchasePrices = {},
        salePrices = {},
        quantityErrors = {},
        priceErrors = {},
        shouldCheckStock = false,
        priceField = 'price',
        productOptions = {},
    } = config;

    const initial = initialItems.length > 0
        ? initialItems
        : [{ product_id: '', quantity: 1, [priceField]: '' }];

    return {
        items: initial,
        productStocks,
        productSkus,
        purchasePrices,
        salePrices,
        quantityErrors,
        priceErrors,
        shouldCheckStock,
        productOptions,
        currentIndex: null,

        init() {
            const parentForm = this.$el.closest('form');
            if (parentForm) {
                parentForm.addEventListener('submit', (event) => this.validateBeforeSubmit(event));
            }

            window.addEventListener('custom-select-opened', (event) => {
                const inputName = event.detail;

                if (!inputName) {
                    this.currentIndex = null;
                    return;
                }

                const match = String(inputName).match(/items\[(\d+)\]/);
                this.currentIndex = match ? Number(match[1]) : null;

                if (this.currentIndex !== null) {
                    const options = this.getAvailableProductOptions(this.currentIndex);
                    const currentItem = this.items[this.currentIndex] || {};

                    window.dispatchEvent(new CustomEvent('custom-select-update', {
                        detail: {
                            name: inputName,
                            options,
                            value: currentItem.product_id ? String(currentItem.product_id) : '',
                        },
                    }));
                }
            });

            window.addEventListener('product-selected', (event) => {
                if (this.currentIndex === null) return;
                this.onProductChange(this.currentIndex, event.detail);
            });
        },

        addItem() {
            this.items.push({
                product_id: '',
                quantity: 1,
                [priceField]: '',
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        onProductChange(index, productId) {
            if (!this.items[index]) return;

            const previousProductId = this.items[index].product_id ?? '';

            if (productId) {
                const duplicateIndex = this.items.findIndex((item, idx) =>
                    idx !== index && String(item.product_id) === String(productId)
                );

                if (duplicateIndex !== -1) {
                    const productName = this.getProductName(productId);
                    const rowNumber = duplicateIndex + 1;

                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            type: 'error',
                            message: `${productName} sudah ada di baris ${rowNumber}. Silakan edit kuantitas di baris tersebut.`,
                        },
                    }));

                    // Kembalikan ke produk sebelumnya
                    this.items[index].product_id = previousProductId || '';

                    if (previousProductId) {
                        this.items[index][priceField] = this.getProductPrice(previousProductId);
                    } else {
                        this.items[index][priceField] = '';
                    }

                    return;
                }
            }

            this.items[index].product_id = productId || '';

            if (productId) {
                this.items[index][priceField] = this.getProductPrice(productId);
            } else {
                this.items[index][priceField] = '';
            }
        },

        getProductPrice(productId) {
            const isUnitCost = priceField === 'unit_cost';
            const source = isUnitCost ? this.purchasePrices : this.salePrices;
            return source[productId] ?? 0;
        },

        getProductStock(productId) {
            if (!productId) return null;
            return Number(this.productStocks[productId] ?? 0);
        },

        getStockClass(productId) {
            const stock = this.getProductStock(productId);
            if (stock === null) return 'text-slate-300 bg-slate-50 border-slate-200';
            if (stock === 0) return 'text-rose-600 bg-rose-50 border-rose-100 font-bold';
            if (stock < 5) return 'text-amber-600 bg-amber-50 border-amber-100 font-bold';
            return 'text-slate-600 bg-slate-100 border-slate-200';
        },

        getProductName(productId) {
            if (!productId) return 'Produk';

            const key = String(productId);
            const raw = this.productOptions && Object.prototype.hasOwnProperty.call(this.productOptions, key)
                ? this.productOptions[key]
                : null;

            if (!raw) return 'Produk';

            if (typeof raw === 'string') return raw;

            if (typeof raw === 'object' && raw !== null) {
                return raw.label ?? raw.name ?? 'Produk';
            }

            return String(raw);
        },

        getAvailableProductOptions(currentIndex) {
            const usedIds = new Set(
                this.items
                    .map((item, idx) => (idx === currentIndex ? null : item.product_id))
                    .filter((id) => id !== null && id !== undefined && id !== '')
                    .map((id) => String(id))
            );

            const result = {};

            Object.keys(this.productOptions || {}).forEach((key) => {
                if (!usedIds.has(String(key))) {
                    result[key] = this.productOptions[key];
                }
            });

            return result;
        },

        isStockInsufficient(index) {
            if (!this.shouldCheckStock) {
                return false;
            }

            const item = this.items[index];

            if (!item || !item.product_id) {
                return false;
            }

            const availableStock = this.getProductStock(item.product_id);
            const requestedQty = Number(item.quantity ?? 0);

            return Number.isFinite(requestedQty) && Number.isFinite(availableStock) && requestedQty > availableStock;
        },

        validateBeforeSubmit(event) {
            if (!this.shouldCheckStock) {
                return;
            }

            const hasShortage = this.items.some((_, idx) => this.isStockInsufficient(idx));

            if (hasShortage) {
                event.preventDefault();
            }
        },

        getItemSubtotal(item) {
            if (!item || !item.product_id) return 0;

            const quantity = Number(item.quantity ?? 0);
            const price = Number(item[priceField] ?? 0);

            if (!Number.isFinite(quantity) || !Number.isFinite(price)) {
                return 0;
            }

            return quantity * price;
        },

        grandTotal() {
            return this.items.reduce((sum, item) => sum + this.getItemSubtotal(item), 0);
        },

        stockError(index) {
            const backendError = this.quantityErrors && Object.prototype.hasOwnProperty.call(this.quantityErrors, index)
                ? this.quantityErrors[index]
                : '';

            if (!this.shouldCheckStock) {
                return backendError;
            }

            const item = this.items[index];

            if (!item || !item.product_id) {
                return backendError;
            }

            if (this.isStockInsufficient(index)) {
                const availableStock = this.getProductStock(item.product_id);
                return `Stok tidak mencukupi. Tersedia: ${this.formatNumber(availableStock)}.`;
            }

            return backendError;
        },

        getProductSku(productId) {
            return this.productSkus[productId] ?? '-';
        },

        priceError(index) {
            return this.priceErrors && Object.prototype.hasOwnProperty.call(this.priceErrors, index)
                ? this.priceErrors[index]
                : '';
        },

        formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }
    };
}
