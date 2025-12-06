

export default function scanModalMixin() {
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
                const backCamera = this.cameras.find(c =>
                    c.label.toLowerCase().includes('back') ||
                    c.label.toLowerCase().includes('belakang') ||
                    c.label.toLowerCase().includes('rear')
                );
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

        // Default implementation - can be overridden
        handleScanSuccess(decodedText, decodedResult) {
            console.log(`Scan result: ${decodedText}`);

            // Standard event
            window.dispatchEvent(new CustomEvent('scan-success', {
                detail: {
                    code: decodedText,
                    type: decodedResult?.result?.format?.formatName || 'qr',
                    mode: this.scanMode
                }
            }));

            this.closeScanModal();
        },

        handleScanFailure(errorMessage) {
            // Default: do nothing or log
            // console.debug(errorMessage);
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
