<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('association_rules', function (Blueprint $table) {
            $table->id();
            $table->string('id_rule', 15)->nullable();
            $table->string('produk_antecedent', 100);
            $table->string('produk_consequent', 100);
            $table->float('nilai_support');
            $table->float('nilai_confidence');
            $table->dateTime('tanggal_proses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('association_rules');
    }
};
