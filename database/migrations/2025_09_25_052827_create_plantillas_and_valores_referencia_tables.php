<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantillas_referencia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->text('descripcion')->nullable();
            $table->json('estructura_formulario');
            $table->timestamps();
        });

        Schema::create('valores_referencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reactivo_id')->constrained('reactivos')->cascadeOnDelete();
            $table->foreignId('grupo_etario_id')->constrained('grupos_etarios')->cascadeOnDelete();
            $table->foreignId('plantilla_referencia_id')->constrained('plantillas_referencia')->cascadeOnDelete();
            $table->json('datos_referencia');
            $table->timestamps();
        });

        Schema::table('reactivos', function (Blueprint $table) {
            $table->foreignId('plantilla_referencia_id')
                  ->nullable()
                  ->after('prueba_id')
                  ->constrained('plantillas_referencia')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('reactivos', function (Blueprint $table) {
            $table->dropForeign(['plantilla_referencia_id']);
            $table->dropColumn('plantilla_referencia_id');
        });
        Schema::dropIfExists('valores_referencia');
        Schema::dropIfExists('plantillas_referencia');
    }
};