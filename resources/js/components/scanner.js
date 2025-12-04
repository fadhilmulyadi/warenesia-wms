import { Html5Qrcode, Html5QrcodeSupportedFormats } from "html5-qrcode";

export class Scanner {
    constructor(elementId, onScanSuccess, onScanFailure) {
        this.elementId = elementId;
        this.onScanSuccess = onScanSuccess;
        this.onScanFailure = onScanFailure;
        this.html5QrCode = null;
        this.isScanning = false;
    }

    async getCameras() {
        try {
            return await Html5Qrcode.getCameras();
        } catch (err) {
            console.error("Error getting cameras", err);
            return [];
        }
    }

    async start(cameraId) {
        if (this.isScanning) {
            await this.stop();
        }

        this.html5QrCode = new Html5Qrcode(this.elementId);

        const config = {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            aspectRatio: 1.0
        };

        try {
            await this.html5QrCode.start(
                cameraId,
                config,
                (decodedText, decodedResult) => {
                    if (this.onScanSuccess) {
                        this.onScanSuccess(decodedText, decodedResult);
                    }
                },
                (errorMessage) => {
                    if (this.onScanFailure) {
                        this.onScanFailure(errorMessage);
                    }
                }
            );
            this.isScanning = true;
        } catch (err) {
            console.error("Error starting scanner", err);
            throw err;
        }
    }

    async stop() {
        if (this.html5QrCode && this.isScanning) {
            try {
                await this.html5QrCode.stop();
                this.html5QrCode.clear();
                this.isScanning = false;
            } catch (err) {
                console.error("Error stopping scanner", err);
            }
        }
    }

    async scanFile(file) {
        if (!this.html5QrCode) {
            this.html5QrCode = new Html5Qrcode(this.elementId);
        }

        try {
            const result = await this.html5QrCode.scanFile(file, true);
            if (this.onScanSuccess) {
                this.onScanSuccess(result, { result: { text: result } }); // Mocking decodedResult structure
            }
            return result;
        } catch (err) {
            console.error("Error scanning file", err);
            if (this.onScanFailure) {
                this.onScanFailure(err);
            }
            throw err;
        }
    }
}
