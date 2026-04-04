{{-- Photo Capture Modal --}}
{{-- Opens on: window event 'open-photo-capture' with detail: { target: 'create'|'edit' } --}}
{{-- Emits on:  window event 'product-photo-captured' with detail: { dataUrl, blob, target } --}}

@push('styles')
    <style>
        #photo-shutter-flash {
            position: absolute;
            inset: 0;
            background: white;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0s;
            border-radius: 0.75rem;
        }

        #photo-shutter-flash.flash {
            opacity: 0.85;
            transition: opacity 0.05s ease-in;
        }

        #photo-shutter-flash.fade-out {
            opacity: 0;
            transition: opacity 0.35s ease-out;
        }
    </style>
@endpush

<div x-data="productPhotoCapture()" @open-photo-capture.window="openCapture($event.detail.target)" x-show="show" x-cloak
    class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
    @click.self="closeCapture()">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                <i class="fa-solid fa-camera text-blue-500"></i>
                Take Product Photo
            </h3>
            <button @click="closeCapture()" type="button"
                class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-5 space-y-4">

            {{-- Error state --}}
            <div x-show="errorMsg"
                class="flex items-start gap-3 bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-700">
                <i class="fa-solid fa-triangle-exclamation mt-0.5 shrink-0"></i>
                <span x-text="errorMsg"></span>
            </div>

            {{-- Live camera view (hidden once captured) --}}
            <div x-show="!captured && !errorMsg" class="relative rounded-xl overflow-hidden bg-black aspect-[4/3]">
                <video x-ref="video" autoplay playsinline muted class="w-full h-full object-cover"></video>
                {{-- Grid overlay --}}
                <div class="absolute inset-0 pointer-events-none"
                    style="
                    background-image: linear-gradient(rgba(255,255,255,.08) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.08) 1px,transparent 1px);
                    background-size: 33.33% 33.33%;
                ">
                </div>
                {{-- Shutter flash --}}
                <div id="photo-shutter-flash"></div>
                {{-- Loading overlay --}}
                <div x-show="!streamReady"
                    class="absolute inset-0 flex flex-col items-center justify-center bg-black/70 text-white gap-3">
                    <svg class="animate-spin w-8 h-8 text-blue-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                        </path>
                    </svg>
                    <span class="text-sm">Starting camera…</span>
                </div>
            </div>

            {{-- Capture preview --}}
            <div x-show="captured" class="relative rounded-xl overflow-hidden bg-black aspect-[4/3]">
                <img :src="capturedDataUrl" x-ref="preview" class="w-full h-full object-cover" alt="Captured photo">
                <div
                    class="absolute top-3 right-3 bg-green-500 text-white text-xs font-semibold px-3 py-1 rounded-full flex items-center gap-1.5 shadow">
                    <i class="fa-solid fa-check"></i> Photo ready
                </div>
            </div>

            {{-- Hidden canvas for capture --}}
            <canvas x-ref="canvas" class="hidden"></canvas>

            {{-- Camera selector --}}
            <div x-show="cameras.length > 1 && !captured">
                <label class="block text-xs font-medium text-gray-500 mb-1">Camera</label>
                <select @change="switchCamera($event.target.value)"
                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <template x-for="cam in cameras" :key="cam.deviceId">
                        <option :value="cam.deviceId" x-text="cam.label || 'Camera ' + cam.deviceId.slice(0,6)"
                            :selected="cam.deviceId === activeCameraId"></option>
                    </template>
                </select>
            </div>

            {{-- Action buttons --}}
            <div x-show="!captured && !errorMsg" class="flex gap-3">
                <button type="button" @click="capture()" :disabled="!streamReady"
                    class="flex-1 flex items-center justify-center gap-2 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white rounded-xl font-semibold text-sm transition-colors shadow-sm">
                    <i class="fa-solid fa-camera text-base"></i>
                    Capture Photo
                </button>
            </div>

            <div x-show="captured" class="flex gap-3">
                <button type="button" @click="retake()"
                    class="flex-1 flex items-center justify-center gap-2 py-3 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl font-medium text-sm transition-colors">
                    <i class="fa-solid fa-rotate-left"></i>
                    Retake
                </button>
                <button type="button" @click="usePhoto()"
                    class="flex-1 flex items-center justify-center gap-2 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold text-sm transition-colors shadow-sm">
                    <i class="fa-solid fa-check"></i>
                    Use Photo
                </button>
            </div>

            <div x-show="errorMsg" class="flex gap-3">
                <button type="button" @click="closeCapture()"
                    class="flex-1 py-3 border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-xl font-medium text-sm transition-colors">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

@push('scripts')
    <script>
        function productPhotoCapture() {
            return {
                show: false,
                target: null,
                streamReady: false,
                captured: false,
                capturedDataUrl: null,
                capturedBlob: null,
                errorMsg: null,
                stream: null,
                cameras: [],
                activeCameraId: null,

                async openCapture(target) {
                    this.target = target;
                    this.captured = false;
                    this.capturedDataUrl = null;
                    this.capturedBlob = null;
                    this.errorMsg = null;
                    this.streamReady = false;
                    this.show = true;
                    await this.$nextTick();
                    await this._startCamera();
                },

                async _startCamera(deviceId = null) {
                    this._stopStream();
                    this.streamReady = false;
                    this.errorMsg = null;

                    const constraints = {
                        video: deviceId ?
                            {
                                deviceId: {
                                    exact: deviceId
                                },
                                width: {
                                    ideal: 1280
                                },
                                height: {
                                    ideal: 960
                                }
                            } :
                            {
                                facingMode: 'environment',
                                width: {
                                    ideal: 1280
                                },
                                height: {
                                    ideal: 960
                                }
                            }
                    };

                    try {
                        this.stream = await navigator.mediaDevices.getUserMedia(constraints);
                        this.$refs.video.srcObject = this.stream;
                        await this.$refs.video.play();
                        this.streamReady = true;

                        // Enumerate cameras after permission granted
                        if (this.cameras.length === 0) {
                            const devices = await navigator.mediaDevices.enumerateDevices();
                            this.cameras = devices.filter(d => d.kind === 'videoinput');
                            if (!this.activeCameraId && this.cameras.length > 0) {
                                const track = this.stream.getVideoTracks()[0];
                                const settings = track.getSettings();
                                this.activeCameraId = settings.deviceId || this.cameras[0].deviceId;
                            }
                        }
                    } catch (err) {
                        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                            this.errorMsg =
                                'Camera access denied. Please allow camera permission in your browser settings and try again.';
                        } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                            this.errorMsg = 'No camera found on this device.';
                        } else {
                            this.errorMsg = 'Could not start camera: ' + err.message;
                        }
                    }
                },

                capture() {
                    if (!this.streamReady || !this.$refs.video) return;

                    const video = this.$refs.video;
                    const canvas = this.$refs.canvas;
                    canvas.width = video.videoWidth || 640;
                    canvas.height = video.videoHeight || 480;
                    canvas.getContext('2d').drawImage(video, 0, 0);

                    // Shutter flash
                    const flash = document.getElementById('photo-shutter-flash');
                    if (flash) {
                        flash.classList.remove('fade-out');
                        flash.classList.add('flash');
                        setTimeout(() => {
                            flash.classList.remove('flash');
                            flash.classList.add('fade-out');
                            setTimeout(() => flash.classList.remove('fade-out'), 400);
                        }, 80);
                    }

                    this.capturedDataUrl = canvas.toDataURL('image/jpeg', 0.92);
                    canvas.toBlob(blob => {
                        this.capturedBlob = blob;
                    }, 'image/jpeg', 0.92);

                    this._stopStream();
                    this.captured = true;
                },

                retake() {
                    this.captured = false;
                    this.capturedDataUrl = null;
                    this.capturedBlob = null;
                    this._startCamera(this.activeCameraId);
                },

                usePhoto() {
                    if (!this.capturedBlob && this.capturedDataUrl) {
                        // Fallback: convert data URL to blob
                        const byteStr = atob(this.capturedDataUrl.split(',')[1]);
                        const arr = new Uint8Array(byteStr.length);
                        for (let i = 0; i < byteStr.length; i++) arr[i] = byteStr.charCodeAt(i);
                        this.capturedBlob = new Blob([arr], {
                            type: 'image/jpeg'
                        });
                    }

                    window.dispatchEvent(new CustomEvent('product-photo-captured', {
                        detail: {
                            dataUrl: this.capturedDataUrl,
                            blob: this.capturedBlob,
                            target: this.target,
                        }
                    }));

                    this.closeCapture();
                },

                closeCapture() {
                    this._stopStream();
                    this.show = false;
                    this.cameras = [];
                    this.activeCameraId = null;
                },

                async switchCamera(deviceId) {
                    this.activeCameraId = deviceId;
                    await this._startCamera(deviceId);
                },

                _stopStream() {
                    if (this.stream) {
                        this.stream.getTracks().forEach(t => t.stop());
                        this.stream = null;
                    }
                    this.streamReady = false;
                },
            };
        }
    </script>
@endpush
