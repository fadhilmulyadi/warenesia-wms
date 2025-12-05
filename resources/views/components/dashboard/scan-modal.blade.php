<div x-show="isScanOpen" x-cloak
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4 sm:p-6"
    x-transition:enter="transition ease-out duration-300" 
    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

    <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col relative"
        @click.away="closeScanModal()" 
        @dragover.prevent="dragOver = true" 
        @dragleave.prevent="dragOver = false"
        @drop.prevent="handleDrop($event)">

        <div class="px-5 py-4 flex justify-between items-center bg-white border-b border-slate-50">
            <div>
                <h3 class="text-base font-bold text-slate-900">Scan QR Code</h3>
                <p class="text-xs text-slate-500" x-text="scanMode === 'incoming' ? 'Mode: Barang Masuk' : 'Mode: Barang Keluar'"></p>
            </div>
            
            <button @click="closeScanModal()" type="button"
                class="p-2 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition active:scale-95">
                <x-lucide-x class="w-6 h-6" />
            </button>
        </div>

        <div class="relative bg-black w-full aspect-[4/5] sm:aspect-square flex items-center justify-center overflow-hidden group">
            
            <div id="reader" class="w-full h-full object-cover"></div>

            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none" x-show="!isFileScanning">
                <div class="w-64 h-64 relative border border-white/30 rounded-3xl">
                    <div class="absolute top-0 left-0 w-6 h-6 border-t-[3px] border-l-[3px] border-white rounded-tl-2xl"></div>
                    <div class="absolute top-0 right-0 w-6 h-6 border-t-[3px] border-r-[3px] border-white rounded-tr-2xl"></div>
                    <div class="absolute bottom-0 left-0 w-6 h-6 border-b-[3px] border-l-[3px] border-white rounded-bl-2xl"></div>
                    <div class="absolute bottom-0 right-0 w-6 h-6 border-b-[3px] border-r-[3px] border-white rounded-br-2xl"></div>
                </div>
                
                <p class="mt-6 text-xs font-medium text-white/80 bg-black/40 px-3 py-1 rounded-full backdrop-blur">
                    Arahkan kamera ke QR Code
                </p>
            </div>

            <button type="button" 
                x-show="cameras.length > 1"
                @click="
                    let currentIndex = cameras.findIndex(c => c.id == selectedCamera);
                    let nextIndex = (currentIndex + 1) % cameras.length;
                    selectedCamera = cameras[nextIndex].id;
                    startScanner();
                "
                class="absolute bottom-5 right-5 z-30 flex items-center justify-center w-12 h-12 rounded-full bg-white/20 backdrop-blur-md border border-white/20 text-white hover:bg-white/30 active:scale-90 transition shadow-lg">
                <x-lucide-refresh-ccw class="w-5 h-5" />
            </button>

            <div class="absolute inset-0 flex items-center justify-center bg-teal-500/90 z-40 backdrop-blur-sm transition-opacity"
                x-show="dragOver" 
                x-transition:enter="duration-200 ease-out"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="duration-150 ease-in"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
                <div class="text-white text-center flex flex-col items-center animate-bounce">
                    <x-lucide-upload-cloud class="w-12 h-12 mb-2" />
                    <p class="font-bold text-lg">Lepaskan File</p>
                </div>
            </div>
        </div>

        <div class="p-4 bg-white border-t border-slate-50">
            <label for="file-upload" class="flex items-center justify-center w-full py-3 gap-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 font-medium text-sm hover:bg-slate-100 hover:border-slate-300 active:scale-[0.98] transition cursor-pointer">
                <x-lucide-image class="w-5 h-5 text-slate-500" />
                <span>Upload QR Code</span>
                <input id="file-upload" type="file" class="hidden" accept="image/*" @change="handleFileUpload($event)" />
            </label>
        </div>
    </div>
</div>