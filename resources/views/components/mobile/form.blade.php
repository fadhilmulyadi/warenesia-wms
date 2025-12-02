@props([
    'formId',
    'saveLabel' => 'Simpan',
    'saveIcon' => null,
    'showDelete' => false,
    'deleteAction' => null,
    'deleteLabel' => 'Hapus',
    'deleteConfirm' => 'Yakin ingin menghapus data ini?',
])

<div class="md:hidden pb-24">
    {{-- Slot isi form --}}
    <div class="space-y-4">
        {{ $fields }}
    </div>

    {{-- Delete button (opsional) --}}
    @if($showDelete && $deleteAction)
        <form
            method="POST"
            action="{{ $deleteAction }}"
            onsubmit="return confirm('{{ $deleteConfirm }}');"
            class="mt-6 px-4"
        >
            @csrf
            @method('DELETE')

            <x-mobile-danger-button type="submit">
                {{ $deleteLabel }}
            </x-mobile-danger-button>
        </form>
    @endif

    {{-- Sticky Save --}}
    <x-mobile-sticky-button :form="$formId" :icon="$saveIcon">
        {{ $saveLabel }}
    </x-mobile-sticky-button>
</div>
