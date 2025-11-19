@php
    /** @var \App\Models\Supplier|null $supplier */

    $supplierName = old('name', optional($supplier)->name);
    $contactPerson = old('contact_person', optional($supplier)->contact_person);
    $email = old('email', optional($supplier)->email);
    $phone = old('phone', optional($supplier)->phone);
    $taxNumber = old('tax_number', optional($supplier)->tax_number);
    $address = old('address', optional($supplier)->address);
    $city = old('city', optional($supplier)->city);
    $country = old('country', optional($supplier)->country ?? 'Indonesia');
    $notes = old('notes', optional($supplier)->notes);
    $isActive = old('is_active', optional($supplier)->is_active ?? true);
@endphp

<div class="space-y-4 text-xs">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
            <label class="block text-[11px] text-slate-600 mb-1">Supplier name *</label>
            <input
                type="text"
                name="name"
                value="{{ $supplierName }}"
                required
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
            >
        </div>

        <div>
            <label class="block text-[11px] text-slate-600 mb-1">Contact person</label>
            <input
                type="text"
                name="contact_person"
                value="{{ $contactPerson }}"
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
            >
        </div>

        <div>
            <label class="block text-[11px] text-slate-600 mb-1">Email</label>
            <input
                type="email"
                name="email"
                value="{{ $email }}"
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
            >
        </div>

        <div>
            <label class="block text-[11px] text-slate-600 mb-1">Phone</label>
            <input
                type="text"
                name="phone"
                value="{{ $phone }}"
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
            >
        </div>

        <div>
            <label class="block text-[11px] text-slate-600 mb-1">Tax number</label>
            <input
                type="text"
                name="tax_number"
                value="{{ $taxNumber }}"
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
            >
        </div>

        <div>
            <label class="block text-[11px] text-slate-600 mb-1">City</label>
            <input
                type="text"
                name="city"
                value="{{ $city }}"
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
            >
        </div>

        <div>
            <label class="block text-[11px] text-slate-600 mb-1">Country</label>
            <input
                type="text"
                name="country"
                value="{{ $country }}"
                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
            >
        </div>

        <div class="flex items-center gap-2 mt-5">
            <input
                id="is_active"
                type="checkbox"
                name="is_active"
                value="1"
                @checked($isActive)
                class="h-3 w-3 rounded border-slate-300 text-teal-600"
            >
            <label for="is_active" class="text-[11px] text-slate-600">
                Active supplier
            </label>
        </div>
    </div>

    <div>
        <label class="block text-[11px] text-slate-600 mb-1">Address</label>
        <textarea
            name="address"
            rows="2"
            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
        >{{ $address }}</textarea>
    </div>

    <div>
        <label class="block text-[11px] text-slate-600 mb-1">Internal notes</label>
        <textarea
            name="notes"
            rows="2"
            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-[11px]"
            placeholder="Optional notes visible only to internal users."
        >{{ $notes }}</textarea>
    </div>
</div>
