export default function staffDashboard({ skuMap, products }) {
    return {
        isScanOpen: false,
        scanMode: 'incoming',
        scanner: null,
        cameras: [],
        selectedCamera: null,
        isFileScanning: false,
        dragOver: false,

        init() {
            this.$watch('isScanOpen', (value) => {
                if (value) {
                    this.$nextTick(() => this.initScanner());
                } else {
                    this.stopScanner();
                }
            });
        },

        async initScanner() {
            if (!this.scanner) {

                if (typeof Scanner === 'undefined') {
                    console.error('Scanner library not loaded');
                    return;
                }

                this.scanner = new Scanner(
                    "reader",
                    (decodedText, decodedResult) => this.handleScanSuccess(decodedText, decodedResult),
                    (errorMessage) => this.handleScanFailure(errorMessage)
                );
            }

            if (this.cameras.length === 0) {
                this.cameras = await this.scanner.getCameras();
            }

            if (this.cameras.length > 0) {
                const backCamera = this.cameras.find(c => c.label.toLowerCase().includes('back') || c.label.toLowerCase().includes('belakang'));
                this.selectedCamera = backCamera ? backCamera.id : this.cameras[0].id;
                this.startScanner();
            } else {
                console.warn("No cameras found.");
            }
        },

        async startScanner() {
            if (this.selectedCamera) {
                this.isFileScanning = false;
                await this.scanner.start(this.selectedCamera);
            }
        },

        async stopScanner() {
            if (this.scanner) {
                await this.scanner.stop();
            }
        },

        handleScanSuccess(decodedText, decodedResult) {
            console.log(`Scan result: ${decodedText}`);

            const sku = decodedText.trim();
            const productId = skuMap[sku];

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
        },

        handleScanFailure(errorMessage) {
            // handle failure (optional logging)
        },

        async handleFileUpload(event) {
            const file = event.target.files[0];
            if (file) {
                this.scanFile(file);
            }
        },

        async handleDrop(event) {
            this.dragOver = false;
            const file = event.dataTransfer.files[0];
            if (file) {
                this.scanFile(file);
            }
        },

        async scanFile(file) {
            this.isFileScanning = true;
            try {
                await this.stopScanner();
                await this.scanner.scanFile(file);
            } catch (err) {
                alert("Gagal memindai file. Pastikan gambar QR code jelas.");
                this.isFileScanning = false;
                this.startScanner();
            }
        },

        openScanModal(mode = 'incoming') {
            this.scanMode = mode;
            this.isScanOpen = true;
        },

        closeScanModal() {
            this.isScanOpen = false;
        }
    };
}
