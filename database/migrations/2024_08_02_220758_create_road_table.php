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
        Schema::create('road', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('rate')->default(0);
            $table->text('note')->nullable();
            $table->string('slug', 255);
            $table->text('via')->nullable();
            $table->decimal('latitudine',30,15)->nullable();
            $table->decimal('longitudine',30,15)->nullable();
            $table->foreignId('travel_id')->constrained()->onDelete('cascade'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('road');
    }
};
