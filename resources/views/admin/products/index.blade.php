<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Produk</h2>
            <a href="{{ route('admin.products.create') }}"
               class="px-4 py-2 bg-gray-800 text-white text-sm rounded-md hover:bg-gray-700">
                + Tambah Produk
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash />

            <form method="GET" action="{{ route('admin.products.index') }}" class="mb-4 flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama atau SKU..."
                    class="w-full sm:w-72 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <x-primary-button>Cari</x-primary-button>
            </form>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stok</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($products as $product)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $product->sku }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $product->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $product->category->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right
                                    {{ $product->stock <= 5 ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                    {{ $product->stock }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                       class="text-indigo-600 hover:underline">Ubah</a>

                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                          class="inline"
                                          onsubmit="return confirm('Hapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ml-3 text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Belum ada produk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $products->links() }}</div>
        </div>
    </div>
</x-app-layout>