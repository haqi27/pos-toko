<div>
    <x-input-label for="category_id" value="Kategori" />
    <select id="category_id" name="category_id" required
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">-- Pilih kategori --</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}"
                @selected(old('category_id', $product->category_id ?? '') == $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="sku" value="Kode Produk (SKU)" />
    <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full"
        :value="old('sku', $product->sku ?? '')" required />
    <x-input-error :messages="$errors->get('sku')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="name" value="Nama Produk" />
    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
        :value="old('name', $product->name ?? '')" required />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4 grid grid-cols-2 gap-4">
    <div>
        <x-input-label for="price" value="Harga (Rp)" />
        <x-text-input id="price" name="price" type="number" min="0" class="mt-1 block w-full"
            :value="old('price', $product->price ?? '')" required />
        <x-input-error :messages="$errors->get('price')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="stock" value="Stok" />
        <x-text-input id="stock" name="stock" type="number" min="0" class="mt-1 block w-full"
            :value="old('stock', $product->stock ?? 0)" required />
        <x-input-error :messages="$errors->get('stock')" class="mt-2" />
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>Simpan</x-primary-button>
    <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
</div>