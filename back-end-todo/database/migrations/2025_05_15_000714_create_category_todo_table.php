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
        Schema::create('category_todo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('to_do_id')->constrained('to_dos')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');

            $table->unique(['to_do_id', 'category_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_todo');
    }
};
