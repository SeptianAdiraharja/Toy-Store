<?php

namespace App\Http\Controllers;

use App\Models\AssociationRule;
use App\Services\AprioriService;
use Illuminate\Http\Request;

class AprioriController extends Controller
{
    protected AprioriService $aprioriService;

    public function __construct(AprioriService $aprioriService)
    {
        $this->aprioriService = $aprioriService;
    }

    public function index()
    {
        $rules = AssociationRule::orderBy('nilai_confidence', 'desc')
            ->orderBy('nilai_support', 'desc')
            ->paginate(10);

        return view('apriori.index', compact('rules'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'min_support' => ['required', 'numeric', 'min:0.1', 'max:100'],
            'min_confidence' => ['required', 'numeric', 'min:0.1', 'max:100'],
        ]);

        $minSupport = (float) $request->input('min_support');
        $minConfidence = (float) $request->input('min_confidence');

        $result = $this->aprioriService->process($minSupport, $minConfidence);

        return view('apriori.result', compact('result'));
    }

    public function hasilRekomendasi(Request $request)
    {
        $query = AssociationRule::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('produk_antecedent', 'like', "%{$search}%")
                  ->orWhere('produk_consequent', 'like', "%{$search}%");
        }

        $rules = $query->orderBy('nilai_confidence', 'desc')
            ->orderBy('nilai_support', 'desc')
            ->paginate(5)
            ->withQueryString();

        return view('apriori.hasil_rekomendasi', compact('rules'));
    }
}
