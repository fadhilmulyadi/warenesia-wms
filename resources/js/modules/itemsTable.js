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
            this.items[index].product_id = productId;

            if (productId) {
                const isUnitCost = priceField === 'unit_cost';
                const price = isUnitCost
                    ? (this.purchasePrices[productId] ?? 0)
                    : (this.salePrices[productId] ?? 0);

                this.items[index][priceField] = price;
            }
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
