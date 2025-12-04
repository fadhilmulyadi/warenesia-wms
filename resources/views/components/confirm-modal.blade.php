@props(['name' => 'confirm-modal'])

<div x-data="{
        show: false,
        action: '',
        method: 'POST',
        title: 'Konfirmasi',
        message: '',
        btnText: 'Ya, Lanjutkan',
        btnClass: 'bg-slate-900 hover:bg-slate-800 text-white',
        type: 'info', // info, success, warning, danger

        open(event) {
            this.show = true;
            this.action = event.detail.action;
            this.method = event.detail.method || 'POST';
            this.title  = event.detail.title || 'Konfirmasi';
            this.message = event.detail.message || 'Apakah Anda yakin ingin melanjutkan tindakan ini?';
            this.btnText = event.detail.btnText || 'Ya, Lanjutkan';
            this.type = event.detail.type || 'info';

            // Set button class based on type if not provided
            if (event.detail.btnClass) {
                this.btnClass = event.detail.btnClass;
            } else {
                this.btnClass = this.getBtnClass(this.type);
            }

            document.body.classList.add('overflow-y-hidden');
        },

        close() {
            this.show = false;
            document.body.classList.remove('overflow-y-hidden');
        },

        getBtnClass(type) {
            switch(type) {
                case 'danger': return 'bg-red-600 hover:bg-red-500 text-white';
                case 'success': return 'bg-emerald-600 hover:bg-emerald-500 text-white';
                case 'warning': return 'bg-amber-500 hover:bg-amber-400 text-white';
                default: return 'bg-slate-900 hover:bg-slate-800 text-white';
            }
        },

        getIconBgClass(type) {
            switch(type) {
                case 'danger': return 'bg-red-100';
                case 'success': return 'bg-emerald-100';
                case 'warning': return 'bg-amber-100';
                default: return 'bg-slate-100';
            }
        },

        getIconTextClass(type) {
            switch(type) {
                case 'danger': return 'text-red-600';
                case 'success': return 'text-emerald-600';
                case 'warning': return 'text-amber-600';
                default: return 'text-slate-600';
            }
        }
    }" x-on:open-confirm-modal.window="open($event)" x-on:close-modal.window="close()"
    x-on:keydown.escape.window="close()" x-show="show" class="relative z-50" style="display: none;">
    {{-- Backdrop --}}
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm" @click="close()">
    </div>

    {{-- Modal Center Wrapper --}}
    <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">

            {{-- Modal Panel --}}
            <div x-show="show" x-transition
                class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all">
                <div class="px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">

                        {{-- Icon --}}
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10"
                            :class="getIconBgClass(type)">
                            {{-- Dynamic Icon based on type --}}
                            <template x-if="type === 'danger'">
                                <x-lucide-alert-triangle class="h-6 w-6 text-red-600" />
                            </template>
                            <template x-if="type === 'warning'">
                                <x-lucide-alert-circle class="h-6 w-6 text-amber-600" />
                            </template>
                            <template x-if="type === 'success'">
                                <x-lucide-check-circle class="h-6 w-6 text-emerald-600" />
                            </template>
                            <template x-if="type === 'info'">
                                <x-lucide-info class="h-6 w-6 text-slate-600" />
                            </template>
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
                        <input type="hidden" name="_method" :value="method">
                        <button type="submit"
                            class="inline-flex w-full justify-center rounded-lg px-3 py-2 text-sm font-semibold shadow-sm sm:ml-3 sm:w-auto"
                            :class="btnClass" x-text="btnText">
                        </button>
                    </form>

                    <button type="button"
                        class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto"
                        @click="close()">
                        Batal
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>