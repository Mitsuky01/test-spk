<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spks', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();
            $t->date('tanggal');
            $t->foreignId('vendor_id')->constrained();
            $t->foreignId('project_id')->constrained();
            $t->foreignId('client_id')->constrained();
            $t->foreignId('pic_id')->constrained();
            $t->string('area')->nullable();
            $t->text('description')->nullable();
            $t->unsignedBigInteger('subtotal')->default(0);
            $t->unsignedBigInteger('discount')->default(0);
            $t->string('tax_type')->default('NON PPN');
            $t->unsignedBigInteger('tax_amount')->default(0);
            $t->unsignedBigInteger('grand_total')->default(0);
            $t->string('status')->default('unpaid'); // unpaid | dp | paid
            $t->text('note')->nullable();
            $t->string('file_path')->nullable();
            $t->string('file_name')->nullable();
            $t->string('ttd1_name')->nullable();
            $t->string('ttd1_title')->nullable();
            $t->string('ttd2_name')->nullable();
            $t->string('ttd2_title')->nullable();
            $t->string('ttd3_name')->nullable();
            $t->string('ttd3_title')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spks');
    }
};
