@props(['name' => 'confirm-delete'])

<div 
    x-data="{
        show: false,
        action: '',
        title: 'Hapus Data?',
        itemName: '',
        message: '',

        open(event) {
            this.show = true;
            this.action = event.detail.action;
            this.title  = event.detail.title || 'Hapus Data?';
            this.itemName = event.detail.itemName || '';

            if (event.detail.message) {
                this.message = event.detail.message;
            }
            else if (this.itemName) {
                this.message = `Apakah Anda yakin ingin menghapus <b>${this.itemName}</b>? Tindakan ini tidak dapat dibatalkan.`;
            }
            else {
                this.message = `Tindakan ini tidak dapat dibatalkan. Data akan hilang permanen.`;
            }

            document.body.classList.add('overflow-y-hidden');
        },

        close() {
            this.show = false;
            document.body.classList.remove('overflow-y-hidden');
        }
    }"

    x-on:open-delete-modal.window="open($event)"
    x-on:close-modal.window="close()"
    x-on:keydown.escape.window="close()"

    x-show="show"
    class="relative z-50"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div 
        x-show="show"
        x-transition.opacity
        class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm"
        @click="close()"
    ></div>

    {{-- Modal Center Wrapper --}}
    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
            
            {{-- Modal Panel --}}
            <div 
                x-show="show"
                x-transition
                class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all"
            >
                <div class="px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">

                        {{-- Icon --}}
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <x-lucide-trash-2 class="h-6 w-6 text-red-600" />
                        </div>

                        <div class="mt-3 sm:ml-4 sm:mt-0">
                            <h3 class="text-base font-semibold leading-6 text-slate-900" x-text="title"></h3>

                            <div class="mt-2">
                                <p class="text-sm text-slate-600" x-html="message"></p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <form :action="action" method="POST">
                        @csrf
                        @method('DELETE')
                        <button 
                            type="submit"
                            class="inline-flex w-full justify-center rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto"
                        >
                            Ya, Hapus
                        </button>
                    </form>

                    <button 
                        type="button"
                        class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto"
                        @click="close()"
                    >
                        Batal
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>