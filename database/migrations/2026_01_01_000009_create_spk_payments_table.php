<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spk_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('spk_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('amount');
            $t->date('paid_at');
            $t->string('note')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spk_payments');
    }
};
