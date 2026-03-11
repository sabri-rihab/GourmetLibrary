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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('author');                           // the "chef"
            $table->string('isbn', 20)->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();         // path to cover image
            $table->year('published_year')->nullable();
            $table->string('publisher')->nullable();
            $table->string('language', 50)->default('Français');
            $table->unsignedSmallInteger('total_copies')->default(1);   // total physical copies
            $table->date('arrival_date')->nullable();          // for "nouveaux arrivages"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
