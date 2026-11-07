<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    // Halaman utama kasir: daftar produk + keranjang
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->where('stock', '>', 0)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(8)
            ->withQueryString();

        [$items, $total] = $this->buildCart();

        return view('kasir.pos', compact('products', 'items', 'total'));
    }

    // Tambah 1 produk ke keranjang
    public function addToCart(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $product = Product::findOrFail($data['product_id']);

        $cart = session('cart', []);
        $qty = ($cart[$product->id] ?? 0) + 1;

        if ($qty > $product->stock) {
            return back()->with('error', "Stok {$product->name} hanya tersisa {$product->stock}.");
        }

        $cart[$product->id] = $qty;
        session(['cart' => $cart]);

        return back();
    }

    // Ubah jumlah barang di keranjang
    public function updateCart(Request $request, Product $product)
    {
        $data = $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        if ($data['qty'] > $product->stock) {
            return back()->with('error', "Stok {$product->name} hanya tersisa {$product->stock}.");
        }

        $cart = session('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id] = $data['qty'];
            session(['cart' => $cart]);
        }

        return back();
    }

    // Hapus satu barang dari keranjang
    public function removeFromCart(Product $product)
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return back();
    }

    // Kosongkan keranjang
    public function clearCart()
    {
        session()->forget('cart');

        return back();
    }

    // Proses pembayaran: simpan transaksi dan kurangi stok
    public function checkout(Request $request)
    {
        $request->validate([
            'paid' => ['required', 'integer', 'min:0'],
        ]);

        $cart = session('cart', []);

        if (empty($cart)) {
            return back()->with('error', 'Keranjang masih kosong.');
        }

        try {
            $transaction = DB::transaction(function () use ($cart, $request) {
                // Kunci baris produk agar tidak diubah transaksi lain di saat bersamaan
                $products = Product::whereIn('id', array_keys($cart))
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $total = 0;
                $lines = [];

                foreach ($cart as $productId => $qty) {
                    $product = $products->get($productId);

                    if (! $product) {
                        throw new \RuntimeException('Ada produk di keranjang yang sudah tidak tersedia.');
                    }

                    if ($product->stock < $qty) {
                        throw new \RuntimeException("Stok {$product->name} tidak cukup (sisa {$product->stock}).");
                    }

                    $subtotal = $product->price * $qty;
                    $total += $subtotal;

                    $lines[] = [
                        'product' => $product,
                        'qty' => $qty,
                        'subtotal' => $subtotal,
                    ];
                }

                $paid = (int) $request->paid;

                if ($paid < $total) {
                    throw new \RuntimeException('Uang yang dibayarkan kurang dari total belanja.');
                }

                $transaction = Transaction::create([
                    'invoice_no' => Transaction::generateInvoiceNo(),
                    'user_id' => Auth::id(),
                    'total_amount' => $total,
                    'paid_amount' => $paid,
                    'change_amount' => $paid - $total,
                ]);

                foreach ($lines as $line) {
                    $transaction->items()->create([
                        'product_id' => $line['product']->id,
                        'product_name' => $line['product']->name,
                        'price' => $line['product']->price,
                        'qty' => $line['qty'],
                        'subtotal' => $line['subtotal'],
                    ]);

                    $line['product']->decrement('stock', $line['qty']);
                }

                return $transaction;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        session()->forget('cart');

        return redirect()
            ->route('kasir.pos')
            ->with('success', "Transaksi {$transaction->invoice_no} berhasil. Kembalian: Rp "
                . number_format($transaction->change_amount, 0, ',', '.'));
    }

    // Mengubah isi session menjadi daftar barang lengkap dengan harga dari database
    private function buildCart(): array
    {
        $cart = session('cart', []);

        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');

        $items = [];
        $total = 0;

        foreach ($cart as $productId => $qty) {
            $product = $products->get($productId);

            if (! $product) {
                continue;
            }

            $subtotal = $product->price * $qty;
            $total += $subtotal;

            $items[] = [
                'product' => $product,
                'qty' => $qty,
                'subtotal' => $subtotal,
            ];
        }

        return [$items, $total];
    }
}