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

<div class="space-y-6">

    {{-- SECTION 1: Informasi Supplier --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 shadow-sm">
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-slate-900">Informasi Supplier</h2>
            <p class="text-[11px] text-slate-500">Nama, kontak, dan data dasar supplier.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Name --}}
            <div>
                <x-input-label for="name" :value="__('Supplier Name')" class="text-xs font-semibold text-slate-700"
                    required />
                <x-text-input id="name" name="name" type="text"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    :value="$supplierName" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            {{-- Contact Person --}}
            <div>
                <x-input-label for="contact_person" :value="__('Contact Person')"
                    class="text-xs font-semibold text-slate-700" />
                <x-text-input id="contact_person" name="contact_person" type="text"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    :value="$contactPerson" />
                <x-input-error class="mt-2" :messages="$errors->get('contact_person')" />
            </div>

            {{-- Email --}}
            <div>
                <x-input-label for="email" :value="__('Email')" class="text-xs font-semibold text-slate-700" required />
                <x-text-input id="email" name="email" type="email"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    :value="$email" required />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>

            {{-- Phone --}}
            <div>
                <x-input-label for="phone" :value="__('Phone')" class="text-xs font-semibold text-slate-700" />
                <x-text-input id="phone" name="phone" type="text"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    :value="$phone" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            {{-- Tax Number --}}
            <div>
                <x-input-label for="tax_number" :value="__('Tax Number (NPWP)')"
                    class="text-xs font-semibold text-slate-700" />
                <x-text-input id="tax_number" name="tax_number" type="text"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    :value="$taxNumber" />
                <x-input-error class="mt-2" :messages="$errors->get('tax_number')" />
            </div>

            {{-- City --}}
            <div>
                <x-input-label for="city" :value="__('City')" class="text-xs font-semibold text-slate-700" />
                <x-text-input id="city" name="city" type="text"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    :value="$city" />
                <x-input-error class="mt-2" :messages="$errors->get('city')" />
            </div>

            {{-- Country --}}
            <div class="md:col-span-2">
                <x-input-label for="country" :value="__('Country')" class="text-xs font-semibold text-slate-700" />
                <x-text-input id="country" name="country" type="text"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    :value="$country" />
                <x-input-error class="mt-2" :messages="$errors->get('country')" />
            </div>
        </div>
    </div>

    {{-- SECTION 2: Alamat & Catatan --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 shadow-sm">
        <div class="mb-4">
            <h2 class="text-sm font-semibold text-slate-900">Alamat & Catatan</h2>
            <p class="text-[11px] text-slate-500">Detail alamat operasional dan catatan internal.</p>
        </div>

        <div class="space-y-4">
            {{-- Address --}}
            <div>
                <x-input-label for="address" :value="__('Address')" class="text-xs font-semibold text-slate-700" />
                <textarea id="address" name="address" rows="3"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500">{{ $address }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>

            {{-- Notes --}}
            <div>
                <x-input-label for="notes" :value="__('Internal Notes')" class="text-xs font-semibold text-slate-700" />
                <textarea id="notes" name="notes" rows="3"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500"
                    placeholder="Optional notes visible only to internal users.">{{ $notes }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('notes')" />
            </div>

            {{-- Is Active --}}
            <div>
                <label for="is_active" class="inline-flex items-center">
                    <input id="is_active" type="checkbox" name="is_active" value="1" @checked($isActive)
                        class="h-4 w-4 text-teal-600 border-slate-300 rounded focus:ring-teal-500">
                    <span class="ml-2 text-xs font-semibold text-slate-700">{{ __('Active Supplier') }}</span>
                </label>
                <p class="text-[11px] text-slate-500 mt-1 ml-6">
                    Inactive suppliers cannot be selected for new restock orders.
                </p>
            </div>
        </div>
    </div>

</div>