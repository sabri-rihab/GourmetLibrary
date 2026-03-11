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
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('copy_number', 20);                 // ex: "COPY-001", "COPY-002"
            $table->enum('condition', ['good', 'degraded', 'damaged', 'lost'])->default('good');
            // good      = parfait état
            // degraded  = pages tachées / reliure légèrement abîmée
            // damaged   = très abîmé (inutilisable mais présent)
            // lost      = perdu
            $table->text('condition_notes')->nullable();       // précisions sur l'état
            $table->boolean('is_available')->default(true);    // false when being borrowed
            $table->timestamps();

            $table->unique(['book_id', 'copy_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_copies');
    }
};
