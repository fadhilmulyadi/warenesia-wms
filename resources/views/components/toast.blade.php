@props(['timeout' => 3000])

<div x-data="{
        notifications: [],

        add(message, type = 'success') {
            const id = Date.now();

            this.notifications.push({
                id,
                message,
                type,
            });

            if (this.notifications.length > 3) {
                this.notifications.shift();
            }

            setTimeout(() => {
                this.remove(id);
            }, {{ $timeout }});
        },

        remove(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        }
    }" x-on:notify.window="add($event.detail.message, $event.detail.type)" x-init="
        @if(session('success'))
            add(@js(session('success')), 'success');
        @endif

        @if(session('status'))
            add(@js(session('status')), 'success');
        @endif
        
        @if(session('error'))
            add(@js(session('error')), 'error');
        @endif
    " class="fixed top-5 right-5 z-50 flex flex-col gap-3 pointer-events-none">
    <template x-for="toast in notifications" :key="toast.id">
        <div x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-5"
            x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-5"
            class="pointer-events-auto w-80 max-w-xs rounded-xl bg-white p-4 shadow-lg border border-slate-100 flex gap-3">
            {{-- Icon --}}
            <div class="shrink-0">
                <template x-if="toast.type === 'success'">
                    <div
                        class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-500">
                        <x-lucide-check class="h-5 w-5" />
                    </div>
                </template>

                <template x-if="toast.type === 'error'">
                    <div class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-500">
                        <x-lucide-x class="h-5 w-5" />
                    </div>
                </template>
            </div>

            {{-- Text --}}
            <div class="flex-1">
                <p class="text-sm font-semibold text-slate-900"
                    x-text="toast.type === 'success' ? 'Berhasil' : 'Gagal'"></p>
                <p class="text-xs text-slate-500 mt-0.5" x-text="toast.message"></p>
            </div>

            {{-- Close --}}
            <button class="text-slate-400 hover:text-slate-600" @click="remove(toast.id)">
                <x-lucide-x class="h-4 w-4" />
            </button>
        </div>
    </template>
</div>