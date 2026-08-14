<?php

namespace App\Http\Controllers;

use App\Models\AssociationRule;
use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KasirController extends Controller
{
    public function index()
    {
        $produks = Produk::where('stok', '>', 0)->orderBy('nama_produk')->get();
        return view('kasir.index', compact('produks'));
    }

    public function getRecommendations(Request $request)
    {
        $productNames = $request->input('product_names', []);

        if (empty($productNames)) {
            return response()->json(['recommendations' => []]);
        }

        // Query association rules where produk_antecedent matches any product in cart
        $rules = AssociationRule::whereIn('produk_antecedent', $productNames)
            ->orderBy('nilai_confidence', 'desc')
            ->orderBy('nilai_support', 'desc')
            ->get();

        $recommendations = [];
        $addedConsequents = [];

        foreach ($rules as $rule) {
            $consequentName = $rule->produk_consequent;

            // Don't recommend products already in cart or already recommended
            if (!in_array($consequentName, $productNames) && !in_array($consequentName, $addedConsequents)) {
                $produk = Produk::where('nama_produk', $consequentName)->first();
                if ($produk) {
                    $addedConsequents[] = $consequentName;
                    $recommendations[] = [
                        'id' => $produk->id,
                        'id_produk' => $produk->id_produk,
                        'nama_produk' => $produk->nama_produk,
                        'kategori' => $produk->kategori,
                        'harga' => $produk->harga,
                        'stok' => $produk->stok,
                        'antecedent' => $rule->produk_antecedent,
                        'confidence' => $rule->nilai_confidence,
                        'support' => $rule->nilai_support,
                    ];
                }
            }
        }

        return response()->json(['recommendations' => $recommendations]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.produk_id' => ['required', 'exists:produks,id'],
            'items.*.jumlah' => ['required', 'integer', 'min:1'],
            'shift' => ['required', 'in:pagi,siang,sore'],
            'bayar' => ['required', 'numeric', 'min:0'],
        ]);

        $items = $request->input('items');
        $shift = $request->input('shift');
        $bayar = $request->input('bayar');

        DB::beginTransaction();
        try {
            $totalHarga = 0;
            $detailsToInsert = [];

            foreach ($items as $itemData) {
                $produk = Produk::lockForUpdate()->find($itemData['produk_id']);

                if ($produk->stok < $itemData['jumlah']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Stok produk '{$produk->nama_produk}' tidak mencukupi (Sisa: {$produk->stok})."
                    ], 422);
                }

                $subtotal = $produk->harga * $itemData['jumlah'];
                $totalHarga += $subtotal;

                // Reduce stock
                $produk->stok -= $itemData['jumlah'];
                $produk->save();

                $detailsToInsert[] = [
                    'produk' => $produk,
                    'jumlah' => $itemData['jumlah'],
                    'subtotal' => $subtotal,
                ];
            }

            if ($bayar < $totalHarga) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Jumlah pembayaran kurang dari total belanja (Total: Rp " . number_format($totalHarga, 0, ',', '.') . ")."
                ], 422);
            }

            // Generate unique transaction ID
            $trxCode = 'TRX-' . Carbon::now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $transaksi = Transaksi::create([
                'id_transaksi' => $trxCode,
                'user_id' => auth()->id(),
                'tanggal_transaksi' => Carbon::now(),
                'total_harga' => $totalHarga,
                'shift' => $shift,
            ]);

            foreach ($detailsToInsert as $idx => $d) {
                DetailTransaksi::create([
                    'id_detail' => 'DTL' . str_pad(rand(100, 9999), 5, '0', STR_PAD_LEFT),
                    'transaksi_id' => $transaksi->id,
                    'produk_id' => $d['produk']->id,
                    'jumlah' => $d['jumlah'],
                    'subtotal' => $d['subtotal'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan!',
                'transaksi_id' => $transaksi->id,
                'redirect_url' => route('transaksis.show', $transaksi->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function hasilRekomendasi(Request $request)
    {
        $query = AssociationRule::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('produk_antecedent', 'like', "%{$search}%")
                ->orWhere('produk_consequent', 'like', "%{$search}%");
            });
        }

        $rules = $query->orderBy('nilai_confidence', 'desc')
            ->orderBy('nilai_support', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('apriori.hasil_rekomendasi', compact('rules'));
    }
}
