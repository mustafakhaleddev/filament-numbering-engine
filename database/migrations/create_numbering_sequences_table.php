<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tenancyEnabled = config('filament-numbering-engine.multi_tenancy.enabled', false);
        $tenantColumn = config('filament-numbering-engine.multi_tenancy.column', 'company_id');

        Schema::create('numbering_sequences', function (Blueprint $table) use ($tenancyEnabled, $tenantColumn) {
            $table->id();

            if ($tenancyEnabled) {
                $table->unsignedBigInteger($tenantColumn)->nullable()->index();
            }

            $table->string('name');
            $table->string('model_type');
            $table->string('attribute');
            $table->string('pattern');
            $table->string('prefix')->nullable();
            $table->string('suffix')->nullable();
            $table->string('reset_frequency')->default('never');
            $table->unsignedTinyInteger('fiscal_year_start_month')->default(1);
            $table->boolean('is_gap_free')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('custom_tokens')->nullable();
            $table->unsignedInteger('initial_value')->default(1);
            $table->timestamps();
            $table->softDeletes();

            if ($tenancyEnabled) {
                $table->unique([$tenantColumn, 'model_type', 'attribute'], 'numbering_sequences_unique');
                $table->index(['model_type', $tenantColumn, 'is_active'], 'numbering_sequences_lookup');
            } else {
                $table->unique(['model_type', 'attribute'], 'numbering_sequences_unique');
                $table->index(['model_type', 'is_active'], 'numbering_sequences_lookup');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numbering_sequences');
    }
};
