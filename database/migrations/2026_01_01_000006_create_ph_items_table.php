<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ph_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ph_id')->constrained()->cascadeOnDelete();
            $t->string('item');
            $t->string('description')->nullable();
            $t->string('product_detail')->nullable();
            $t->unsignedInteger('qty')->default(1);
            $t->unsignedBigInteger('price')->default(0);
            $t->unsignedBigInteger('total')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ph_items');
    }
};
