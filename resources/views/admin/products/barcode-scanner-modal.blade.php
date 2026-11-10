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
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gray-50">
                <div>
                    <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                        <i class="fa-solid fa-barcode text-blue-600"></i>
                        <span>Scan Barcode</span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Point camera at product barcode (EAN, UPC, Code 128)</p>
                </div>
                <button @click="closeScanner()"
                    class="text-gray-400 hover:text-gray-600 transition-colors p-1.5 rounded-lg hover:bg-gray-100">
                    <i class="fa-solid fa-times text-lg"></i>
                </button>
            </div>

            {{-- Camera Preview --}}
            <div class="px-5 pt-4 pb-2">
                <div class="relative bg-black rounded-xl overflow-hidden" style="aspect-ratio: 4/3;">
                    <video x-ref="cameraVideo" class="w-full h-full object-cover" autoplay muted playsinline></video>

                    {{-- Top Status / Controls Overlay --}}
                    <div class="absolute top-2.5 inset-x-2.5 flex items-center justify-between pointer-events-none z-10" x-show="!cameraError">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-black/65 backdrop-blur-sm rounded-full text-xs text-white">
                            <span class="w-2 h-2 rounded-full" :class="isScanning ? 'bg-emerald-400 animate-pulse' : 'bg-amber-400'"></span>
                            <span x-text="scanStatusText"></span>
                        </div>
                        <div class="flex items-center gap-1 pointer-events-auto">
                            <button type="button" x-show="hasMultipleCameras" @click="switchCamera()" title="Switch camera"
                                class="p-1.5 bg-black/65 hover:bg-black/90 backdrop-blur-sm rounded-full text-white text-xs transition-colors">
                                <i class="fa-solid fa-camera-rotate"></i>
                            </button>
                            <button type="button" x-show="hasTorch" @click="toggleTorch()" :title="torchOn ? 'Turn off flash' : 'Turn on flash'"
                                class="p-1.5 bg-black/65 hover:bg-black/90 backdrop-blur-sm rounded-full text-xs transition-colors"
                                :class="torchOn ? 'text-amber-300' : 'text-white'">
                                <i class="fa-solid fa-bolt"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Scan overlay --}}
                    <div class="absolute inset-0 pointer-events-none" x-show="!cameraError && !scanSuccess">
                        <div class="product-scan-line"></div>
                        <div class="product-scan-corner product-scan-corner-tl"></div>
                        <div class="product-scan-corner product-scan-corner-tr"></div>
                        <div class="product-scan-corner product-scan-corner-bl"></div>
                        <div class="product-scan-corner product-scan-corner-br"></div>
                        {{-- Centre target line --}}
                        <div class="absolute inset-x-0 top-1/2 -mt-px h-px bg-blue-400/50 pointer-events-none"></div>

                        {{-- Guidance pill --}}
                        <div class="absolute bottom-2.5 inset-x-2.5 text-center">
                            <span class="inline-block px-2.5 py-1 bg-black/65 backdrop-blur-sm rounded-full text-[11px] text-gray-200">
                                Center barcode horizontally & hold steady (~15 cm)
                            </span>
                        </div>
                    </div>

                    {{-- Success flash --}}
                    <div x-show="scanSuccess" x-cloak
                        class="absolute inset-0 flex items-center justify-center bg-emerald-600/90 pointer-events-none transition-all duration-200">
                        <div class="text-center px-4">
                            <i class="fa-solid fa-circle-check text-white text-5xl animate-bounce"></i>
                            <p class="text-white font-bold text-lg mt-2 tracking-wide font-mono" x-text="scannedCode"></p>
                            <p class="text-emerald-100 text-xs mt-1">Barcode Captured Successfully!</p>
                        </div>
                    </div>

                    {{-- Error overlay --}}
                    <div x-show="cameraError"
                        class="absolute inset-0 flex flex-col items-center justify-center bg-black/90 p-5 z-20">
                        <i class="fa-solid fa-video-slash text-red-400 text-3xl mb-3"></i>
                        <p class="text-white text-sm text-center leading-relaxed" x-text="cameraError"></p>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-2.5 text-xs text-gray-500 px-1">
                    <span class="inline-flex items-center gap-1">
                        <i class="fa-solid fa-circle-info text-blue-500"></i>
                        <span>Supports EAN-13, EAN-8, UPC, Code 128, QR Code</span>
                    </span>
                    <span class="text-gray-400 text-[11px]" x-show="engineName" x-text="'Engine: ' + engineName"></span>
                </div>
            </div>

            {{-- Manual Entry fallback --}}
            <div class="px-5 py-2">
                <label class="block text-xs font-medium text-gray-600 mb-1">Manual Barcode Input</label>
                <div class="relative">
                    <input type="text" x-model="manualCode" @keydown.enter.prevent="submitManual()"
                        placeholder="Type barcode numbers manually..."
                        class="w-full pl-3 pr-20 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                    <button type="button" @click="submitManual()" :disabled="!manualCode.trim()"
                        class="absolute right-1 top-1 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 disabled:opacity-40 transition-colors">
                        Apply
                    </button>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-5 pb-4 pt-2">
                <button type="button" @click="closeScanner()"
                    class="w-full py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
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
                isScanning: false,
                scanStatusText: 'Starting camera...',
                engineName: '',
                hasMultipleCameras: false,
                hasTorch: false,
                torchOn: false,
                facingMode: 'environment',
                _scanTarget: 'default',
                _stream: null,
                _interval: null,
                _zxingReader: null,
                _zxingControls: null,

                openScanner(target) {
                    this._scanTarget = target;
                    this.cameraOpen = true;
                    this.cameraError = '';
                    this.scanSuccess = false;
                    this.scannedCode = '';
                    this.manualCode = '';
                    this.isScanning = false;
                    this.scanStatusText = 'Starting camera...';
                    this.engineName = '';
                    this.torchOn = false;
                    this.$nextTick(() => this._startCamera());
                },

                closeScanner() {
                    this.cameraOpen = false;
                    this.isScanning = false;
                    this._stopAll();
                },

                submitManual() {
                    const code = this.manualCode.trim();
                    if (!code) return;
                    this._onDetected(code);
                },

                _playBeep() {
                    try {
                        const AudioCtx = window.AudioContext || window.webkitAudioContext;
                        if (!AudioCtx) return;
                        const ctx = new AudioCtx();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(1750, ctx.currentTime);
                        gain.gain.setValueAtTime(0.18, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.12);
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.12);
                    } catch {}
                },

                _onDetected(code) {
                    if (!code || this.scanSuccess) return;
                    this.scannedCode = code;
                    this.scanSuccess = true;
                    this.isScanning = false;
                    this.scanStatusText = 'Barcode detected!';
                    this._playBeep();
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
                    if (this._zxingControls) {
                        try {
                            this._zxingControls.stop();
                        } catch {}
                        this._zxingControls = null;
                    }
                    if (this._zxingReader) {
                        this._zxingReader = null;
                    }
                    if (this._stream) {
                        this._stream.getTracks().forEach(t => t.stop());
                        this._stream = null;
                    }
                    this.torchOn = false;
                },

                async _checkDevices() {
                    try {
                        if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices) {
                            const devices = await navigator.mediaDevices.enumerateDevices();
                            const videoDevices = devices.filter(d => d.kind === 'videoinput');
                            this.hasMultipleCameras = videoDevices.length > 1;
                        }
                    } catch {}
                },

                async switchCamera() {
                    this.facingMode = (this.facingMode === 'environment') ? 'user' : 'environment';
                    await this._startCamera();
                },

                async toggleTorch() {
                    if (!this._stream) return;
                    const track = this._stream.getVideoTracks()[0];
                    if (track && track.getCapabilities && track.getCapabilities().torch) {
                        this.torchOn = !this.torchOn;
                        try {
                            await track.applyConstraints({ advanced: [{ torch: this.torchOn }] });
                        } catch {}
                    }
                },

                async _startCamera() {
                    this._stopAll();
                    this.isScanning = false;
                    this.scanStatusText = 'Connecting to camera...';
                    try {
                        this._stream = await navigator.mediaDevices.getUserMedia({
                            video: {
                                facingMode: {
                                    ideal: this.facingMode
                                },
                                width: {
                                    ideal: 1280
                                }
                            }
                        });
                        const video = this.$refs.cameraVideo;
                        video.srcObject = this._stream;
                        await video.play();

                        // Check torch capability
                        const track = this._stream.getVideoTracks()[0];
                        this.hasTorch = !!(track && track.getCapabilities && track.getCapabilities().torch);

                        await this._checkDevices();
                        await this._startScanLoop();
                    } catch (err) {
                        this.isScanning = false;
                        this.cameraError = err.name === 'NotAllowedError' ?
                            'Camera access denied. Please allow camera permissions in your browser settings, or enter the barcode manually below.' :
                            'Could not start camera: ' + err.message;
                    }
                },

                async _startScanLoop() {
                    const video = this.$refs.cameraVideo;
                    this.scanStatusText = 'Scanning active...';
                    this.isScanning = true;

                    // 1. Check if native BarcodeDetector is available AND supports 1D product barcode formats
                    let canUseNative = false;
                    let nativeDetector = null;
                    if ('BarcodeDetector' in window) {
                        try {
                            const detected = await BarcodeDetector.getSupportedFormats();
                            const supported1D = ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39'].filter(f => detected.includes(f));
                            if (supported1D.length > 0) {
                                const desired = [...supported1D, 'qr_code', 'data_matrix', 'code_93', 'itf'].filter(f => detected.includes(f));
                                nativeDetector = new BarcodeDetector({ formats: desired });
                                canUseNative = true;
                                this.engineName = 'Native BarcodeDetector';
                            }
                        } catch {
                            canUseNative = false;
                        }
                    }

                    if (canUseNative && nativeDetector) {
                        this._interval = setInterval(async () => {
                            if (!this.cameraOpen || !video.readyState || video.readyState < 2) return;
                            try {
                                const barcodes = await nativeDetector.detect(video);
                                if (barcodes.length > 0 && barcodes[0].rawValue) {
                                    this._onDetected(barcodes[0].rawValue);
                                }
                            } catch {}
                        }, 200);
                        return;
                    }

                    // 2. Fallback to ZXing Browser (Universal: works on macOS Chrome, Safari, Firefox, iOS, Android, etc.)
                    try {
                        this.scanStatusText = 'Loading scanner engine...';
                        await this._loadScript('https://cdn.jsdelivr.net/npm/@zxing/browser@0.1.5/umd/zxing-browser.min.js');
                        this.engineName = 'ZXing Engine';
                        this.scanStatusText = 'Scanning active... Point at barcode';
                        this.isScanning = true;

                        this._zxingReader = new ZXingBrowser.BrowserMultiFormatReader();
                        this._zxingControls = await this._zxingReader.decodeFromVideoElement(video, (result, error) => {
                            if (!this.cameraOpen) return;
                            if (result && result.getText()) {
                                this._onDetected(result.getText());
                            }
                        });
                    } catch (err) {
                        // Backup CDN fallback
                        try {
                            await this._loadScript('https://unpkg.com/@zxing/browser@0.1.5/umd/zxing-browser.min.js');
                            this.engineName = 'ZXing Engine';
                            this.scanStatusText = 'Scanning active... Point at barcode';
                            this.isScanning = true;

                            this._zxingReader = new ZXingBrowser.BrowserMultiFormatReader();
                            this._zxingControls = await this._zxingReader.decodeFromVideoElement(video, (result, error) => {
                                if (!this.cameraOpen) return;
                                if (result && result.getText()) {
                                    this._onDetected(result.getText());
                                }
                            });
                        } catch (backupErr) {
                            this.isScanning = false;
                            this.cameraError = 'Barcode scanning engine could not be loaded. Please type the barcode manually below.';
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
