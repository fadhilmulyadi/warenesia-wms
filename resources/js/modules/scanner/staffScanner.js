import scanModalMixin from './scanModalMixin.js';

export default function staffDashboard({ skuMap, products }) {
    const mixin = scanModalMixin();

    return {
        ...mixin,

        init() {
            // Initialize mixin logic
            mixin.init.call(this);
        },

        handleScanSuccess(decodedText, decodedResult) {
            console.log(`Scan result: ${decodedText}`);

            const sku = decodedText.trim();
            const productId = skuMap[sku];

            // Standard event emission
            window.dispatchEvent(new CustomEvent('scan-success', {
                detail: {
                    code: sku,
                    type: decodedResult?.result?.format?.formatName || 'qr',
                    mode: this.scanMode
                }
            }));

            if (productId) {
                window.dispatchEvent(new CustomEvent('barcode-prefill', {
                    detail: {
                        mode: this.scanMode,
                        product_id: productId
                    }
                }));

                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        message: 'Produk berhasil ditambahkan',
                        type: 'success'
                    }
                }));

                this.closeScanModal();
            } else {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        message: `Produk dengan SKU '${sku}' tidak ditemukan.`,
                        type: 'error'
                    }
                }));
            }
        }
    };
}
