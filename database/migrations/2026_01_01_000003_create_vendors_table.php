<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('alamat')->nullable();
            $t->string('phone')->nullable();
            $t->string('npwp')->nullable();
            $t->string('nama_bank')->nullable();
            $t->string('no_rekening')->nullable();
            $t->string('nama_rekening')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
