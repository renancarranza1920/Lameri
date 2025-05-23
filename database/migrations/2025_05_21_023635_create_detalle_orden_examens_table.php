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
        Schema::create('detalle_orden_examens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')
                ->constrained('ordens')
                ->onDelete('cascade');
            $table->foreignId('examen_id')
                ->constrained('examens')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_orden_examens');
    }
};
