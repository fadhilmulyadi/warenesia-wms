@php
    /** @var \App\Models\Category|null $category */
    $isEdit = isset($category) && $category->exists;
@endphp

<div class="space-y-4">
    <div>
        <label class="text-[11px] font-medium text-slate-600 mb-1 block">Category name *</label>
        <input
            type="text"
            name="name"
            value="{{ old('name', $isEdit ? $category->name : '') }}"
            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
            required
        >
    </div>

    <div>
        <label class="text-[11px] font-medium text-slate-600 mb-1 block">Description</label>
        <textarea
            name="description"
            rows="3"
            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"
            placeholder="Optional"
        >{{ old('description', $isEdit ? $category->description : '') }}</textarea>
    </div>
</div>
