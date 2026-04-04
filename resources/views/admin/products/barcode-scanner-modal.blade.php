{{-- ============================================================
     Shared Barcode/Camera Scanner for Products
     Listens for: open-barcode-scanner (window event) with { target: 'create'|'edit' }
     Dispatches:  barcode-scanned (window event) with { code, target }
 ============================================================ --}}

@push('styles')
    <style>
        .product-scan-line {
            position: absolute;
            left: 10%;
            right: 10%;
            height: 2px;
            background: rgba(37, 99, 235, .85);
            box-shadow: 0 0 8px 3px rgba(37, 99, 235, .5);
            animation: productScan 2s linear infinite;
            top: 10%;
        }

        @keyframes productScan {
            0% {
                top: 10%
            }

            50% {
                top: 90%
            }

            100% {
                top: 10%
            }
        }

        .product-scan-corner {
            position: absolute;
            width: 22px;
            height: 22px;
            border-color: #2563eb;
            border-style: solid;
        }

        .product-scan-corner-tl {
            top: 8px;
            left: 8px;
            border-width: 3px 0 0 3px;
            border-radius: 3px 0 0 0;
        }

        .product-scan-corner-tr {
            top: 8px;
            right: 8px;
            border-width: 3px 3px 0 0;
            border-radius: 0 3px 0 0;
        }

        .product-scan-corner-bl {
            bottom: 8px;
            left: 8px;
            border-width: 0 0 3px 3px;
            border-radius: 0 0 0 3px;
        }

        .product-scan-corner-br {
            bottom: 8px;
            right: 8px;
            border-width: 0 3px 3px 0;
            border-radius: 0 0 3px 0;
        }

        .product-beep-flash {
            animation: beepFlash 0.3s ease;
        }

        @keyframes beepFlash {
            0% {
                opacity: 1
            }

            50% {
                opacity: 0.1
            }

            100% {
                opacity: 1
            }
        }
    </style>
@endpush

<div x-data="productBarcodeScanner()" @open-barcode-scanner.window="openScanner($event.detail.target ?? 'default')"
    @keydown.escape.window="cameraOpen && closeScanner()">

    {{-- Camera Scanner Modal --}}
    <div x-show="cameraOpen" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center bg-black/75 p-4"
        style="display:none">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col overflow-hidden" @click.stop>

            {{-- Header --}}
            <div
                class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-600 to-blue-700">
                <div>
                    <h3 class="text-base font-semibold text-white">
                        <i class="fa-solid fa-barcode mr-2"></i>Scan Barcode
                    </h3>
                    <p class="text-xs text-blue-200 mt-0.5">Point your camera at a product barcode</p>
                </div>
                <button @click="closeScanner()"
                    class="text-blue-200 hover:text-white transition-colors p-1 rounded-lg hover:bg-blue-600">
                    <i class="fa-solid fa-times text-lg"></i>
                </button>
            </div>

            {{-- Camera Preview --}}
            <div class="px-5 pt-5 pb-3">
                <div class="relative bg-black rounded-xl overflow-hidden" style="aspect-ratio: 4/3;">
                    <video x-ref="cameraVideo" class="w-full h-full object-cover" autoplay muted playsinline></video>

                    {{-- Scan overlay --}}
                    <div class="absolute inset-0 pointer-events-none" x-show="!cameraError">
                        <div class="product-scan-line"></div>
                        <div class="product-scan-corner product-scan-corner-tl"></div>
                        <div class="product-scan-corner product-scan-corner-tr"></div>
                        <div class="product-scan-corner product-scan-corner-bl"></div>
                        <div class="product-scan-corner product-scan-corner-br"></div>
                        {{-- Centre target line --}}
                        <div class="absolute inset-x-0 top-1/2 -mt-px h-px bg-blue-400/40 pointer-events-none"></div>
                    </div>

                    {{-- Success flash --}}
                    <div x-show="scanSuccess" x-cloak
                        class="absolute inset-0 flex items-center justify-center bg-green-500/80 pointer-events-none">
                        <div class="text-center">
                            <i class="fa-solid fa-check text-white text-5xl"></i>
                            <p class="text-white font-semibold mt-2" x-text="scannedCode"></p>
                        </div>
                    </div>

                    {{-- Error overlay --}}
                    <div x-show="cameraError"
                        class="absolute inset-0 flex flex-col items-center justify-center bg-black/85 p-5">
                        <i class="fa-solid fa-video-slash text-white text-3xl mb-3"></i>
                        <p class="text-white text-sm text-center leading-relaxed" x-text="cameraError"></p>
                    </div>
                </div>

                <p class="text-xs text-gray-400 text-center mt-3">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    Supports EAN-13, EAN-8, UPC-A, Code 128, Code 39, QR Code
                </p>
            </div>

            {{-- Manual Entry fallback --}}
            <div class="px-5 pb-3">
                <div class="relative">
                    <input type="text" x-model="manualCode" @keydown.enter.prevent="submitManual()"
                        placeholder="Or type barcode manually and press Enter…"
                        class="w-full pl-4 pr-24 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button @click="submitManual()" :disabled="!manualCode.trim()"
                        class="absolute right-1 top-1 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 disabled:opacity-40 transition-colors">
                        Use
                    </button>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-5 pb-5">
                <button @click="closeScanner()"
                    class="w-full py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                    <i class="fa-solid fa-times mr-1"></i>Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function productBarcodeScanner() {
            return {
                cameraOpen: false,
                cameraError: '',
                scanSuccess: false,
                scannedCode: '',
                manualCode: '',
                _scanTarget: 'default',
                _stream: null,
                _interval: null,
                _zxingReader: null,

                openScanner(target) {
                    this._scanTarget = target;
                    this.cameraOpen = true;
                    this.cameraError = '';
                    this.scanSuccess = false;
                    this.scannedCode = '';
                    this.manualCode = '';
                    this.$nextTick(() => this._startCamera());
                },

                closeScanner() {
                    this.cameraOpen = false;
                    this._stopAll();
                },

                submitManual() {
                    const code = this.manualCode.trim();
                    if (!code) return;
                    this._onDetected(code);
                },

                _onDetected(code) {
                    this.scannedCode = code;
                    this.scanSuccess = true;
                    this._stopAll();
                    setTimeout(() => {
                        this.cameraOpen = false;
                        this.scanSuccess = false;
                        window.dispatchEvent(new CustomEvent('barcode-scanned', {
                            detail: {
                                code,
                                target: this._scanTarget
                            }
                        }));
                    }, 500);
                },

                _stopAll() {
                    if (this._interval) {
                        clearInterval(this._interval);
                        this._interval = null;
                    }
                    if (this._zxingReader) {
                        try {
                            this._zxingReader.reset();
                        } catch {}
                        this._zxingReader = null;
                    }
                    if (this._stream) {
                        this._stream.getTracks().forEach(t => t.stop());
                        this._stream = null;
                    }
                },

                async _startCamera() {
                    this._stopAll();
                    try {
                        this._stream = await navigator.mediaDevices.getUserMedia({
                            video: {
                                facingMode: {
                                    ideal: 'environment'
                                },
                                width: {
                                    ideal: 1280
                                }
                            }
                        });
                        const video = this.$refs.cameraVideo;
                        video.srcObject = this._stream;
                        await video.play();
                        await this._startScanLoop();
                    } catch (err) {
                        this.cameraError = err.name === 'NotAllowedError' ?
                            'Camera access denied. Please allow camera permissions in your browser settings, or type the barcode manually below.' :
                            'Could not start camera: ' + err.message;
                    }
                },

                async _startScanLoop() {
                    if ('BarcodeDetector' in window) {
                        const formats = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'code_93',
                            'qr_code', 'data_matrix', 'itf'
                        ];
                        let supported = formats;
                        try {
                            const detected = await BarcodeDetector.getSupportedFormats();
                            supported = formats.filter(f => detected.includes(f));
                        } catch {}
                        const detector = new BarcodeDetector({
                            formats: supported.length ? supported : ['ean_13', 'code_128']
                        });
                        const video = this.$refs.cameraVideo;
                        this._interval = setInterval(async () => {
                            if (!this.cameraOpen || !video.readyState || video.readyState < 2) return;
                            try {
                                const barcodes = await detector.detect(video);
                                if (barcodes.length > 0) this._onDetected(barcodes[0].rawValue);
                            } catch {}
                        }, 300);
                    } else {
                        // Fallback: @zxing/browser (Firefox + others)
                        try {
                            await this._loadScript(
                            'https://cdn.jsdelivr.net/npm/@zxing/browser@0.1.5/umd/index.min.js');
                            const video = this.$refs.cameraVideo;
                            this._zxingReader = new ZXingBrowser.BrowserMultiFormatReader();
                            this._zxingReader.decodeFromVideoElement(video, (result) => {
                                if (result && this.cameraOpen) this._onDetected(result.getText());
                            });
                        } catch {
                            this.cameraError =
                                'Barcode scanning is not supported in this browser. Please use Chrome, Edge, or type the barcode manually below.';
                        }
                    }
                },

                _loadScript(src) {
                    return new Promise((resolve, reject) => {
                        if (document.querySelector(`script[src="${src}"]`)) {
                            resolve();
                            return;
                        }
                        const s = document.createElement('script');
                        s.src = src;
                        s.onload = resolve;
                        s.onerror = reject;
                        document.head.appendChild(s);
                    });
                },
            };
        }
    </script>
@endpush
