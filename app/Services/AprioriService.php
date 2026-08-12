<?php

namespace App\Services;

use App\Models\AssociationRule;
use App\Models\Transaksi;
use Carbon\Carbon;

class AprioriService
{
    /**
     * Process Apriori Algorithm and save generated strong association rules.
     *
     * @param float $minSupportPercent Minimum support in percent (e.g. 20 for 20%)
     * @param float $minConfidencePercent Minimum confidence in percent (e.g. 60 for 60%)
     * @return array Step-by-step breakdown of calculations
     */
    public function process(float $minSupportPercent, float $minConfidencePercent): array
    {
        // 1. Fetch transactions with distinct product names per transaction
        $transaksis = Transaksi::with(['detailTransaksis.produk'])->get();

        $transactionBaskets = [];
        foreach ($transaksis as $t) {
            $items = [];
            foreach ($t->detailTransaksis as $detail) {
                if ($detail->produk && !in_array($detail->produk->nama_produk, $items)) {
                    $items[] = $detail->produk->nama_produk;
                }
            }
            if (!empty($items)) {
                $transactionBaskets[] = array_values(array_unique($items));
            }
        }

        $totalTransactions = count($transactionBaskets);
        if ($totalTransactions === 0) {
            return [
                'total_transactions' => 0,
                'min_support' => $minSupportPercent,
                'min_confidence' => $minConfidencePercent,
                'candidate_1' => [],
                'frequent_1' => [],
                'candidate_2' => [],
                'frequent_2' => [],
                'association_rules' => [],
                'saved_rules' => 0
            ];
        }

        // 2. Candidate 1-Itemset calculation
        $itemCounts = [];
        foreach ($transactionBaskets as $basket) {
            foreach ($basket as $item) {
                if (!isset($itemCounts[$item])) {
                    $itemCounts[$item] = 0;
                }
                $itemCounts[$item]++;
            }
        }

        $candidate1 = [];
        $frequent1 = [];
        foreach ($itemCounts as $item => $count) {
            $support = round(($count / $totalTransactions) * 100, 2);
            $isFrequent = ($support >= $minSupportPercent);

            $itemData = [
                'item' => $item,
                'count' => $count,
                'support' => $support,
                'is_frequent' => $isFrequent
            ];

            $candidate1[] = $itemData;
            if ($isFrequent) {
                $frequent1[$item] = $count;
            }
        }

        // 3. Candidate 2-Itemset calculation
        $frequent1Keys = array_keys($frequent1);
        $candidate2 = [];
        $frequent2 = [];

        for ($i = 0; $i < count($frequent1Keys); $i++) {
            for ($j = $i + 1; $j < count($frequent1Keys); $j++) {
                $itemA = $frequent1Keys[$i];
                $itemB = $frequent1Keys[$j];

                // Count co-occurrences
                $pairCount = 0;
                foreach ($transactionBaskets as $basket) {
                    if (in_array($itemA, $basket) && in_array($itemB, $basket)) {
                        $pairCount++;
                    }
                }

                $pairSupport = round(($pairCount / $totalTransactions) * 100, 2);
                $isFrequent = ($pairSupport >= $minSupportPercent);

                $pairData = [
                    'item1' => $itemA,
                    'item2' => $itemB,
                    'count' => $pairCount,
                    'support' => $pairSupport,
                    'is_frequent' => $isFrequent
                ];

                $candidate2[] = $pairData;
                if ($isFrequent) {
                    $frequent2[] = $pairData;
                }
            }
        }

        // 4. Form Association Rules & Calculate Confidence
        $rules = [];
        $rulesToSave = [];
        $now = Carbon::now();

        foreach ($frequent2 as $freq) {
            $itemA = $freq['item1'];
            $itemB = $freq['item2'];
            $pairCount = $freq['count'];
            $pairSupport = $freq['support'];

            // Rule 1: A -> B
            $countA = $frequent1[$itemA] ?? 1;
            $confAtoB = round(($pairCount / $countA) * 100, 2);
            $isValidA = ($confAtoB >= $minConfidencePercent);

            $rule1 = [
                'antecedent' => $itemA,
                'consequent' => $itemB,
                'support' => $pairSupport,
                'confidence' => $confAtoB,
                'is_valid' => $isValidA
            ];
            $rules[] = $rule1;
            if ($isValidA) {
                $rulesToSave[] = [
                    'antecedent' => $itemA,
                    'consequent' => $itemB,
                    'support' => $pairSupport,
                    'confidence' => $confAtoB
                ];
            }

            // Rule 2: B -> A
            $countB = $frequent1[$itemB] ?? 1;
            $confBtoA = round(($pairCount / $countB) * 100, 2);
            $isValidB = ($confBtoA >= $minConfidencePercent);

            $rule2 = [
                'antecedent' => $itemB,
                'consequent' => $itemA,
                'support' => $pairSupport,
                'confidence' => $confBtoA,
                'is_valid' => $isValidB
            ];
            $rules[] = $rule2;
            if ($isValidB) {
                $rulesToSave[] = [
                    'antecedent' => $itemB,
                    'consequent' => $itemA,
                    'support' => $pairSupport,
                    'confidence' => $confBtoA
                ];
            }
        }

        // Save generated strong rules to database
        // First truncate or clear existing rules to store updated rules
        AssociationRule::truncate();
        $savedCount = 0;
        foreach ($rulesToSave as $idx => $r) {
            AssociationRule::create([
                'id_rule' => 'AR' . str_pad($idx + 1, 4, '0', STR_PAD_LEFT),
                'produk_antecedent' => $r['antecedent'],
                'produk_consequent' => $r['consequent'],
                'nilai_support' => $r['support'],
                'nilai_confidence' => $r['confidence'],
                'tanggal_proses' => $now,
            ]);
            $savedCount++;
        }

        return [
            'total_transactions' => $totalTransactions,
            'min_support' => $minSupportPercent,
            'min_confidence' => $minConfidencePercent,
            'candidate_1' => $candidate1,
            'frequent_1' => $frequent1,
            'candidate_2' => $candidate2,
            'frequent_2' => $frequent2,
            'association_rules' => $rules,
            'saved_rules' => $savedCount
        ];
    }
}
