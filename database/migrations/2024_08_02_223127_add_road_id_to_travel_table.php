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
        Schema::table('travel', function (Blueprint $table) {
            $table->unsignedBigInteger('road_id')->nullable(); // Aggiungi la colonna road_id
            $table->foreign('road_id')->references('id')->on('roads')->onDelete('cascade'); // Imposta la relazione se necessario
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('travel', function (Blueprint $table) {
            $table->dropForeign(['road_id']); // Rimuovi la foreign key se esiste
            $table->dropColumn('road_id'); // Rimuovi la colonna road_id
        });
    }
};
