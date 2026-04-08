<?php

namespace Wezlo\FilamentNumberingEngine\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Wezlo\FilamentNumberingEngine\Enums\ResetFrequency;
use Wezlo\FilamentNumberingEngine\Events\NumberGenerated;
use Wezlo\FilamentNumberingEngine\Models\NumberingCounter;
use Wezlo\FilamentNumberingEngine\Models\NumberingSequence;

class NumberingEngine
{
    public function __construct(
        protected PatternParser $parser,
    ) {}

    /**
     * Generate the next number for a model, consuming a counter value.
     */
    public function generate(Model $model, ?NumberingSequence $sequence = null, ?string $attribute = null): string
    {
        $sequence ??= $this->resolveSequence($model, $attribute);
        $scopeKey = $this->buildScopeKey($sequence, $model);

        if ($sequence->is_gap_free) {
            return $this->generateGapFree($model, $sequence, $scopeKey);
        }

        return $this->generateStandard($model, $sequence, $scopeKey);
    }

    /**
     * Preview the next number without consuming it.
     */
    public function preview(Model $model, ?NumberingSequence $sequence = null, ?string $attribute = null): string
    {
        $sequence ??= $this->resolveSequence($model, $attribute);
        $scopeKey = $this->buildScopeKey($sequence, $model);

        $counter = NumberingCounter::where('numbering_sequence_id', $sequence->id)
            ->where('scope_key', $scopeKey)
            ->first();

        $nextValue = ($counter?->current_value ?? ($sequence->initial_value - 1)) + 1;

        return $this->parser->parse($sequence->pattern, [
            'model' => $model,
            'sequence_value' => $nextValue,
            'sequence' => $sequence,
        ]);
    }

    /**
     * Resolve the active numbering sequence for a model and optional attribute.
     */
    public function resolveSequence(Model $model, ?string $attribute = null): NumberingSequence
    {
        return NumberingSequence::forModel($model, $attribute)->firstOrFail();
    }

    /**
     * Generate a number using standard (non-gap-free) mode.
     * Uses atomic increment — gaps may occur if the model save fails.
     */
    protected function generateStandard(Model $model, NumberingSequence $sequence, string $scopeKey): string
    {
        $counter = NumberingCounter::firstOrCreate(
            [
                'numbering_sequence_id' => $sequence->id,
                'scope_key' => $scopeKey,
            ],
            ['current_value' => $sequence->initial_value - 1],
        );

        $counter->increment('current_value');
        $counter->refresh();

        $number = $this->parser->parse($sequence->pattern, [
            'model' => $model,
            'sequence_value' => $counter->current_value,
            'sequence' => $sequence,
        ]);

        event(new NumberGenerated($model, $sequence->attribute, $number, $sequence));

        return $number;
    }

    /**
     * Generate a number using gap-free mode.
     * Uses DB transaction with row-level locking.
     * The caller (HasNumbering trait) must ensure the model save
     * happens within the same transaction.
     */
    public function generateGapFree(Model $model, NumberingSequence $sequence, string $scopeKey): string
    {
        $counter = NumberingCounter::lockForUpdate()
            ->firstOrCreate(
                [
                    'numbering_sequence_id' => $sequence->id,
                    'scope_key' => $scopeKey,
                ],
                ['current_value' => $sequence->initial_value - 1],
            );

        // Re-lock after potential create
        $counter = NumberingCounter::where('id', $counter->id)->lockForUpdate()->first();
        $counter->increment('current_value');
        $counter->refresh();

        $number = $this->parser->parse($sequence->pattern, [
            'model' => $model,
            'sequence_value' => $counter->current_value,
            'sequence' => $sequence,
        ]);

        event(new NumberGenerated($model, $sequence->attribute, $number, $sequence));

        return $number;
    }

    /**
     * Build the scope key for counter lookup.
     * Combines tenant ID with the period segment based on reset frequency.
     */
    public function buildScopeKey(NumberingSequence $sequence, Model $model): string
    {
        $tenantPart = 'global';

        if (config('filament-numbering-engine.multi_tenancy.enabled', false)) {
            $column = config('filament-numbering-engine.multi_tenancy.column', 'company_id');

            if ($tenantId = $model->{$column} ?? null) {
                $tenantPart = "tenant_{$tenantId}";
            }
        }

        $periodPart = $this->computePeriodSegment($sequence);

        return "{$tenantPart}:{$periodPart}";
    }

    /**
     * Compute the period segment based on reset frequency and fiscal year config.
     */
    protected function computePeriodSegment(NumberingSequence $sequence): string
    {
        $now = Carbon::now();

        return match ($sequence->reset_frequency) {
            ResetFrequency::Never => 'all',
            ResetFrequency::Daily => $now->format('Y-m-d'),
            ResetFrequency::Monthly => $now->format('Y-m'),
            ResetFrequency::Yearly => (string) $this->computeFiscalYear($now, $sequence->fiscal_year_start_month),
        };
    }

    /**
     * Compute the fiscal year number given a date and fiscal year start month.
     *
     * If fiscal year starts in April (month 4), then:
     * - Jan 2026 belongs to fiscal year 2025
     * - Apr 2026 belongs to fiscal year 2026
     */
    protected function computeFiscalYear(Carbon $date, int $startMonth): int
    {
        if ($startMonth <= 1) {
            return $date->year;
        }

        return $date->month >= $startMonth
            ? $date->year
            : $date->year - 1;
    }
}
