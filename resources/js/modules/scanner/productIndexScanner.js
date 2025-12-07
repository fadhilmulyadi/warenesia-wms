import scanModalMixin from './scanModalMixin.js';

export default function productIndexScanner() {
    const mixin = scanModalMixin();

    return {
        ...mixin,
        scannedBarcode: '',

        init() {
            mixin.init.call(this);
        },

        handleScan(code) {
            this.scannedBarcode = code;

            // We need to submit the form. 
            // Since we are using x-model on a hidden input inside the form, 
            // we can look for the form that contains the hidden input.

            this.$nextTick(() => {
                const hiddenInput = document.querySelector('input[name="barcode"]');
                if (hiddenInput && hiddenInput.form) {
                    hiddenInput.form.submit();
                } else {
                    console.error("Form with barcode input not found");
                }
            });
        }
    };
}
