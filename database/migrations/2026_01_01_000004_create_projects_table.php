<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();
            $t->string('nama');
            $t->foreignId('client_id')->constrained();
            $t->foreignId('pic_id')->constrained();
            $t->string('area');
            $t->text('description')->nullable();
            $t->string('status')->default('open'); // open | done | canceled
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
