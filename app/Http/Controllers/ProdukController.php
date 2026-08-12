<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $query = Produk::query();

        if ($search) {
            $query->where('nama_produk', 'like', "%{$search}%")
                  ->orWhere('id_produk', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
        }

        $produks = $query->latest()->paginate(10)->withQueryString();
        return view('produks.index', compact('produks', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_produk' => ['nullable', 'string', 'max:10', 'unique:produks,id_produk'],
            'nama_produk' => ['required', 'string', 'max:100'],
            'kategori' => ['required', 'string', 'max:50'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
        ]);

        if (empty($validated['id_produk'])) {
            $lastProduk = Produk::latest('id')->first();
            $nextId = $lastProduk ? ($lastProduk->id + 1) : 1;
            $validated['id_produk'] = 'PRD' . str_pad($nextId, 3, '0', STR_PAD_LEFT);
        }

        Produk::create($validated);

        return redirect()->route('produks.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Produk $produk)
    {
        $validated = $request->validate([
            'id_produk' => ['required', 'string', 'max:10', Rule::unique('produks', 'id_produk')->ignore($produk->id)],
            'nama_produk' => ['required', 'string', 'max:100'],
            'kategori' => ['required', 'string', 'max:50'],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
        ]);

        $produk->update($validated);

        return redirect()->route('produks.index')->with('success', 'Data produk berhasil diperbarui.');
    }

    public function destroy(Produk $produk)
    {
        $produk->delete();
        return redirect()->route('produks.index')->with('success', 'Produk berhasil dihapus.');
    }
}
