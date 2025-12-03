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
    $isActive = old('is_active', optional($supplier)->is_active ?? false);
@endphp

<div class="space-y-6 text-xs">
    {{-- Registration Information --}}
    <div>
        <h3 class="text-sm font-semibold text-slate-900 mb-3">Registration Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="name" :value="__('Supplier Name')" required />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="$supplierName"
                    required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="contact_person" :value="__('Contact Person')" />
                <x-text-input id="contact_person" name="contact_person" type="text" class="mt-1 block w-full"
                    :value="$contactPerson" />
                <x-input-error class="mt-2" :messages="$errors->get('contact_person')" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" required />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="$email" required />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            <div>
                <x-input-label for="phone" :value="__('Phone')" />
                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="$phone" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>
        </div>
    </div>

    <hr class="border-slate-100">

    {{-- Company Details --}}
    <div>
        <h3 class="text-sm font-semibold text-slate-900 mb-3">Company Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="tax_number" :value="__('Tax Number (NPWP)')" />
                <x-text-input id="tax_number" name="tax_number" type="text" class="mt-1 block w-full"
                    :value="$taxNumber" />
                <x-input-error class="mt-2" :messages="$errors->get('tax_number')" />
            </div>

            <div>
                <x-input-label for="city" :value="__('City')" />
                <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="$city" />
                <x-input-error class="mt-2" :messages="$errors->get('city')" />
            </div>

            <div>
                <x-input-label for="country" :value="__('Country')" />
                <x-text-input id="country" name="country" type="text" class="mt-1 block w-full" :value="$country" />
                <x-input-error class="mt-2" :messages="$errors->get('country')" />
            </div>

            <div class="md:col-span-2">
                <x-input-label for="address" :value="__('Address')" />
                <textarea id="address" name="address" rows="2"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-xs">{{ $address }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>
        </div>
    </div>

    <hr class="border-slate-100">

    {{-- Administrative --}}
    <div>
        <h3 class="text-sm font-semibold text-slate-900 mb-3">Administrative</h3>
        <div class="space-y-4">
            <div>
                <label for="is_active" class="inline-flex items-center">
                    <input id="is_active" type="checkbox" name="is_active" value="1" @checked($isActive)
                        class="rounded border-slate-300 text-teal-600 shadow-sm focus:ring-teal-500">
                    <span class="ml-2 text-xs text-slate-600">{{ __('Active Supplier') }}</span>
                </label>
                <p class="text-[10px] text-slate-500 mt-1 ml-6">
                    Inactive suppliers cannot be selected for new restock orders.
                </p>
            </div>

            <div>
                <x-input-label for="notes" :value="__('Internal Notes')" />
                <textarea id="notes" name="notes" rows="3"
                    class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-xs"
                    placeholder="Optional notes visible only to internal users.">{{ $notes }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('notes')" />
            </div>
        </div>
    </div>
</div>