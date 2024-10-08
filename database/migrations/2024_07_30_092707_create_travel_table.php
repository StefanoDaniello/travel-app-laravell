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
        Schema::create('travel', function (Blueprint $table) {
            $table->id();
            $table->string('name',255);
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('image')->nullable();
            $table->text('meal')->nullable();
            $table->text('curiosity')->nullable();
            $table->text('luogo')->nullable();
            $table->decimal('latitudine',30,15)->nullable();
            $table->decimal('longitudine',30,15)->nullable();
            $table->string('slug',255);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel');
    }
};
