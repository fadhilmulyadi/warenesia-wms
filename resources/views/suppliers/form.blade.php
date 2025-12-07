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
@endphp

<div class="space-y-6">

    {{-- SECTION: Information --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 shadow-sm">
        <h2 class="text-base font-semibold text-slate-900 mb-4">Informasi Supplier</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Name --}}
            <div class="space-y-1">
                <x-input-label for="name" :value="__('Nama Supplier')" class="text-sm font-semibold text-slate-700"
                    required />
                <x-text-input id="name" name="name" type="text"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 placeholder:text-slate-400"
                    :value="$supplierName" required autofocus placeholder="Masukkan nama supplier" />
                <x-input-error class="mt-1" :messages="$errors->get('name')" />
            </div>

            {{-- Contact Person --}}
            <div class="space-y-1">
                <x-input-label for="contact_person" :value="__('Kontak Person')"
                    class="text-sm font-semibold text-slate-700" />
                <x-text-input id="contact_person" name="contact_person" type="text"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 placeholder:text-slate-400"
                    :value="$contactPerson" placeholder="Nama kontak yang bisa dihubungi" />
                <x-input-error class="mt-1" :messages="$errors->get('contact_person')" />
            </div>

            {{-- Email --}}
            <div class="space-y-1">
                <x-input-label for="email" :value="__('Email')" class="text-sm font-semibold text-slate-700" required />
                <x-text-input id="email" name="email" type="email"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 placeholder:text-slate-400"
                    :value="$email" required placeholder="email@contoh.com" />
                <x-input-error class="mt-1" :messages="$errors->get('email')" />
            </div>

            {{-- Phone --}}
            <div class="space-y-1">
                <x-input-label for="phone" :value="__('No. Telepon')" class="text-sm font-semibold text-slate-700" />
                <x-text-input id="phone" name="phone" type="text"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 placeholder:text-slate-400"
                    :value="$phone" placeholder="Contoh: 08123456789" />
                <x-input-error class="mt-1" :messages="$errors->get('phone')" />
            </div>

            {{-- Tax Number --}}
            <div class="space-y-1">
                <x-input-label for="tax_number" :value="__('NPWP')" class="text-sm font-semibold text-slate-700" />
                <x-text-input id="tax_number" name="tax_number" type="text"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 placeholder:text-slate-400"
                    :value="$taxNumber" placeholder="Nomor Pokok Wajib Pajak" />
                <x-input-error class="mt-1" :messages="$errors->get('tax_number')" />
            </div>

            {{-- City --}}
            <div class="space-y-1">
                <x-input-label for="city" :value="__('Kota')" class="text-sm font-semibold text-slate-700" />
                <x-text-input id="city" name="city" type="text"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 placeholder:text-slate-400"
                    :value="$city" placeholder="Nama kota" />
                <x-input-error class="mt-1" :messages="$errors->get('city')" />
            </div>

            {{-- Country --}}
            <div class="space-y-1 md:col-span-2">
                <x-input-label for="country" :value="__('Negara')" class="text-sm font-semibold text-slate-700" />
                <x-text-input id="country" name="country" type="text"
                    class="mt-1 block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 placeholder:text-slate-400"
                    :value="$country" placeholder="Nama negara" />
                <x-input-error class="mt-1" :messages="$errors->get('country')" />
            </div>
        </div>
    </div>

    {{-- SECTION: Address & Notes --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-6 shadow-sm">
        <h2 class="text-base font-semibold text-slate-900 mb-4">Alamat & Catatan</h2>

        <div class="space-y-4">

            {{-- Address --}}
            <div class="space-y-1">
                <x-input-label for="address" :value="__('Alamat')" class="text-sm font-semibold text-slate-700" />
                <textarea id="address" name="address" rows="3"
                    class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 placeholder:text-slate-400"
                    placeholder="Alamat lengkap operasional">{{ $address }}</textarea>
                <x-input-error class="mt-1" :messages="$errors->get('address')" />
            </div>

            {{-- Notes --}}
            <div class="space-y-1">
                <x-input-label for="notes" :value="__('Catatan Internal')"
                    class="text-sm font-semibold text-slate-700" />
                <textarea id="notes" name="notes" rows="3"
                    class="mt-1 block w-full rounded-xl border-slate-200 text-sm shadow-sm focus:border-teal-500 focus:ring-teal-500 placeholder:text-slate-400"
                    placeholder="Catatan internal untuk supplier ini">{{ $notes }}</textarea>
                <x-input-error class="mt-1" :messages="$errors->get('notes')" />
            </div>

        </div>
    </div>

</div>