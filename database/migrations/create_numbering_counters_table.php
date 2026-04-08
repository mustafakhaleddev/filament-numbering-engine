<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('numbering_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('numbering_sequence_id')->constrained()->cascadeOnDelete();
            $table->string('scope_key');
            $table->unsignedBigInteger('current_value')->default(0);
            $table->timestamps();

            $table->unique(['numbering_sequence_id', 'scope_key'], 'numbering_counters_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numbering_counters');
    }
};
