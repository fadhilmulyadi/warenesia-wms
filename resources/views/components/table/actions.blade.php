<div 
    x-data="{ 
        open: false,
        dropUp: false,
        showActionModal: false,
        id: 'dropdown-' + Math.random().toString(36).substr(2, 9) 
    }"
    @close-other-dropdowns.window="if($event.detail.id !== id) open = false"
    @click.outside="open = false; showActionModal = false"
    @keydown.escape.window="open = false; showActionModal = false"
    
    class="relative inline-block text-left"
    data-prevent-row-navigation="true"
>
    {{-- Tombol Titik Tiga --}}
    <button
        @click.stop="
            if (showActionModal) {
                showActionModal = false;
                return;
            }

            // Deteksi: jika hanya ada 1 baris di tabel, pakai modal saja
            let useModal = false;
            const row = $el.closest('tr');
            if (row && row.parentElement) {
                const rows = Array.from(row.parentElement.querySelectorAll('tr'));
                if (rows.length === 1) {
                    useModal = true;
                }
            }

            if (useModal) {
                open = false;
                showActionModal = true;
                return;
            }

            open = !open; 
            if(open) {
                $dispatch('close-other-dropdowns', { id });

                const rect = $el.getBoundingClientRect();
                const spaceBelow = window.innerHeight - rect.bottom;
                const spaceAbove = rect.top;
                const dropdownHeight = 180;

                let forceDropUp = false;
                if (row && row.parentElement) {
                    const rows = Array.from(row.parentElement.querySelectorAll('tr'));
                    const index = rows.indexOf(row);
                    const indexFromBottom = rows.length - index - 1;
                    if (indexFromBottom <= 1) {
                        forceDropUp = true;
                    }
                }

                if (forceDropUp) {
                    dropUp = true;
                } else if (spaceBelow >= dropdownHeight) {
                    dropUp = false;
                } else if (spaceAbove >= dropdownHeight) {
                    dropUp = true;
                } else {
                    dropUp = false;
                }
            }
        "
        type="button"
        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-200 transition-colors"
        :class="open ? 'bg-slate-200 text-slate-600' : ''"
    >
        <x-lucide-more-vertical class="h-4 w-4" />
    </button>

    {{-- Dropdown Menu --}}
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"

        :class="dropUp 
            ? 'bottom-full mb-2 origin-bottom-right' 
            : 'mt-2 origin-top-right'"

        class="absolute right-0 w-40 rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 
               z-50 max-h-48 overflow-y-auto"
        style="display: none;"
        @click.stop
    >
        <div class="py-1 divide-y divide-slate-100">
            {{ $slot }}
        </div>
    </div>

    {{-- Modal Aksi untuk kasus 1 baris tabel --}}
    <div
        x-show="showActionModal"
        x-transition.opacity
        class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40"
        @click.self="showActionModal = false"
        style="display: none;"
    >
        <div class="w-full max-w-xs rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 max-h-[70vh] overflow-y-auto">
            <div 
                class="py-2 divide-y divide-slate-100"
                @click.stop="showActionModal = false"
            >
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
