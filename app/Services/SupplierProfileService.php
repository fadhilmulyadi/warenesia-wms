<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\User;

class SupplierProfileService
{
    public function sync(User $user, array $data = []): Supplier
    {
        $companyName = $data['company_name']
            ?? $data['name']
            ?? $user->department
            ?? $user->name;

        $contactPerson = $data['supplier_name']
            ?? $data['contact_person']
            ?? $data['name']
            ?? $user->name;

        $notes = $data['category_of_goods']
            ?? $data['department']
            ?? null;

        $existingSupplier = Supplier::where('user_id', $user->id)->first();

        $nameCheck = Supplier::where('name', $companyName);
        if ($existingSupplier) {
            $nameCheck->where('id', '!=', $existingSupplier->id);
        }

        if ($nameCheck->exists()) {
            $companyName = $companyName . ' #' . $user->id;
        }

        return Supplier::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $companyName,
                'contact_person' => $contactPerson,
                'email' => $data['email'] ?? $user->email,
                'phone' => $data['phone'] ?? null,
                'notes' => $notes,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'country' => $data['country'] ?? 'Indonesia',
                'tax_number' => $data['tax_number'] ?? null,
            ]
        );
    }
}
