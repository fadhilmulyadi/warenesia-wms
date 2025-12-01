<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\User;

class SupplierProfileService
{
    public function sync(User $user, array $data = [], bool $active = true): Supplier
    {
        $companyName = $data['company_name']
            ?? $data['name']
            ?? $user->department
            ?? $user->name;

        $contactPerson = $data['supplier_name']
            ?? $data['name']
            ?? $user->name;

        $notes = $data['category_of_goods']
            ?? $data['department']
            ?? null;

        if (Supplier::where('name', $companyName)->where('id', '!=', $user->id)->exists()) {
            $companyName = $companyName . ' #' . $user->id;
        }

        return Supplier::updateOrCreate(
            ['id' => $user->id],
            [
                'name' => $companyName,
                'contact_person' => $contactPerson,
                'email' => $user->email,
                'phone' => $data['phone'] ?? null,
                'is_active' => $active,
                'notes' => $notes,
            ]
        );
    }
}