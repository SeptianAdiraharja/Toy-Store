<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssociationRule extends Model
{
    use HasFactory;

    protected $table = 'association_rules';

    protected $fillable = [
        'id_rule',
        'produk_antecedent',
        'produk_consequent',
        'nilai_support',
        'nilai_confidence',
        'tanggal_proses',
    ];

    protected $casts = [
        'nilai_support' => 'float',
        'nilai_confidence' => 'float',
        'tanggal_proses' => 'datetime',
    ];
}
