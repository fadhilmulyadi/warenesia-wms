<div x-show="isScanOpen" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
    x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] relative"
        @click.away="closeScanModal()" @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
        @drop.prevent="handleDrop($event)">
        {{-- Header --}}
        <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <div>
                <h3 class="font-bold text-slate-800">Scan Barcode</h3>
                <p class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold"
                    x-text="scanMode === 'incoming' ? 'Barang Masuk' : 'Barang Keluar'"></p>
            </div>
            <button @click="closeScanModal()" type="button"
                class="p-1 rounded-full hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18" />
                    <path d="m6 6 12 12" />
                </svg>
            </button>
        </div>

        {{-- Camera Selection --}}
        <div class="px-4 py-2 bg-white border-b border-slate-100" x-show="cameras.length > 1">
            <select x-model="selectedCamera" @change="startScanner()"
                class="w-full text-sm border-slate-200 rounded-lg focus:ring-teal-500 focus:border-teal-500">
                <template x-for="camera in cameras" :key="camera.id">
                    <option :value="camera.id" x-text="camera.label"></option>
                </template>
            </select>
        </div>

        {{-- Camera Preview Area --}}
        <div class="relative bg-black w-full aspect-[4/3] flex items-center justify-center overflow-hidden">

            {{-- Elemen Video (Target Html5Qrcode) --}}
            <div id="reader" class="w-full h-full"></div>

            {{-- Overlay Kotak Scan (Pemanis UI) --}}
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none" x-show="!isFileScanning">
                <div class="w-48 h-48 border-2 border-teal-400/70 rounded-lg relative">
                    <div class="absolute top-0 left-0 w-4 h-4 border-t-4 border-l-4 border-teal-400 -mt-1 -ml-1"></div>
                    <div class="absolute top-0 right-0 w-4 h-4 border-t-4 border-r-4 border-teal-400 -mt-1 -mr-1"></div>
                    <div class="absolute bottom-0 left-0 w-4 h-4 border-b-4 border-l-4 border-teal-400 -mb-1 -ml-1">
                    </div>
                    <div class="absolute bottom-0 right-0 w-4 h-4 border-b-4 border-r-4 border-teal-400 -mb-1 -mr-1">
                    </div>

                    {{-- Garis Merah Bergerak --}}
                    <div
                        class="absolute inset-x-0 top-1/2 h-0.5 bg-red-500/80 shadow-[0_0_8px_rgba(239,68,68,0.8)] animate-pulse">
                    </div>
                </div>
            </div>

            <p class="absolute bottom-4 text-xs text-white/80 bg-black/40 px-2 py-1 rounded" x-show="!isFileScanning">
                Arahkan kamera ke Barcode atau Drop File
            </p>
        </div>

        {{-- File Upload & Footer --}}
        <div class="p-4 bg-white border-t border-slate-100 space-y-3">
            <div class="flex items-center justify-center w-full">
                <label for="dropzone-file"
                    class="flex flex-col items-center justify-center w-full h-12 border-2 border-slate-300 border-dashed rounded-lg cursor-pointer bg-slate-50 hover:bg-slate-100 transition">
                    <div class="flex items-center gap-2">
                        <x-lucide-image class="w-5 h-5 text-slate-500" />
                        <p class="text-sm text-slate-500">Upload gambar QR</p>
                    </div>
                    <input id="dropzone-file" type="file" class="hidden" accept="image/*"
                        @change="handleFileUpload($event)" />
                </label>
            </div>

            <button @click="closeScanModal()" type="button"
                class="w-full py-2.5 rounded-xl border border-slate-200 text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
                Batal
            </button>
        </div>

        {{-- Drag & Drop Overlay (Covering entire modal) --}}
        <div class="absolute inset-0 bg-teal-500/90 flex flex-col items-center justify-center backdrop-blur-sm z-50"
            x-show="dragOver" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95" style="display: none;">
            <x-lucide-upload class="w-16 h-16 text-white mb-4 animate-bounce" />
            <p class="text-xl font-bold text-white">Lepaskan File Disini</p>
            <p class="text-white/80 text-sm mt-2">Scan QR Code dari gambar</p>
        </div>
    </div>
</div>