<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spk_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('spk_id')->constrained()->cascadeOnDelete();
            $t->string('item');
            $t->string('description')->nullable();
            $t->string('quotation_number')->nullable();
            $t->string('project')->nullable();
            $t->unsignedInteger('qty')->default(1);
            $t->unsignedBigInteger('price')->default(0);
            $t->unsignedBigInteger('total')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spk_items');
    }
};
