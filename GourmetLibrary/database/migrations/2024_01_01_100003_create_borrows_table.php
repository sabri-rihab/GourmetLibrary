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
        Schema::create('borrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();       // the gourmand
            $table->foreignId('book_copy_id')->constrained()->cascadeOnDelete();  // specific physical copy
            $table->date('borrowed_at');                       // date d'emprunt
            $table->date('due_date');                          // date de retour prévue
            $table->date('returned_at')->nullable();           // null = not yet returned
            $table->enum('status', ['active', 'returned', 'overdue', 'lost'])->default('active');
            $table->text('return_notes')->nullable();          // notes at return (condition on return)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrows');
    }
};
