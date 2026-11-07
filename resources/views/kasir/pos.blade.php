<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kasir</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash />

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-100 p-4 text-red-800">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- KIRI: daftar produk --}}
                <div class="lg:col-span-2">
                    <form method="GET" action="{{ route('kasir.pos') }}" class="mb-4 flex gap-2">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama atau SKU..." autofocus
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <x-primary-button>Cari</x-primary-button>
                    </form>

                    <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stok</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($products as $product)
                                    <tr>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="text-gray-900">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $product->sku }} &middot; {{ $product->category->name }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-900">
                                            Rp {{ number_format($product->price, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right text-gray-500">{{ $product->stock }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <form action="{{ route('kasir.pos.cart.add') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <button type="submit"
                                                    class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-500">
                                                    + Tambah
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">
                                            Produk tidak ditemukan atau stok habis.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $products->links() }}</div>
                </div>

                {{-- KANAN: keranjang dan pembayaran --}}
                <div>
                    <div class="bg-white shadow-sm sm:rounded-lg p-4">
                        <h3 class="font-semibold text-gray-800 mb-3">Keranjang</h3>

                        @forelse ($items as $item)
                            <div class="py-3 border-b border-gray-100">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-900">{{ $item['product']->name }}</span>
                                    <span class="text-gray-900">
                                        Rp {{ number_format($item['subtotal'], 0, ',', '.') }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    @ Rp {{ number_format($item['product']->price, 0, ',', '.') }}
                                </div>

                                <div class="mt-2 flex items-center gap-2">
                                    <form action="{{ route('kasir.pos.cart.update', $item['product']) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="qty" min="1" value="{{ $item['qty'] }}"
                                            onchange="this.form.submit()"
                                            class="w-20 text-sm border-gray-300 rounded-md shadow-sm">
                                    </form>

                                    <form action="{{ route('kasir.pos.cart.remove', $item['product']) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-600 hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Keranjang masih kosong.</p>
                        @endforelse

                        <div class="mt-4 flex justify-between text-lg font-semibold text-gray-900">
                            <span>Total</span>
                            <span>Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>

                        {{-- Form pembayaran --}}
                        <form action="{{ route('kasir.pos.checkout') }}" method="POST" class="mt-4"
                              x-data="{ paid: '{{ old('paid') }}', total: {{ $total }} }">
                            @csrf

                            <x-input-label for="paid" value="Uang Dibayar (Rp)" />
                            <input type="number" id="paid" name="paid" min="0" x-model.number="paid"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required>

                            <button type="button" @click="paid = total"
                                class="mt-2 text-sm text-indigo-600 hover:underline">
                                Uang pas
                            </button>

                            <div class="mt-3 flex justify-between text-sm text-gray-700">
                                <span>Kembalian</span>
                                <span x-text="paid !== '' && paid >= total ? 'Rp ' + (paid - total).toLocaleString('id-ID') : '-'"></span>
                            </div>

                            <button type="submit" @disabled(empty($items))
                                class="mt-4 w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                Bayar
                            </button>
                        </form>

                        {{-- Form terpisah (jangan di dalam form pembayaran!) --}}
                        @if (! empty($items))
                            <form action="{{ route('kasir.pos.cart.clear') }}" method="POST" class="mt-2"
                                  onsubmit="return confirm('Kosongkan keranjang?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full text-sm text-gray-600 hover:underline">
                                    Kosongkan keranjang
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>