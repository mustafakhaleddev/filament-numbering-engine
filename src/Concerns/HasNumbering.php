<?php

namespace Wezlo\FilamentNumberingEngine\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Wezlo\FilamentNumberingEngine\Models\NumberingSequence;
use Wezlo\FilamentNumberingEngine\Services\NumberingEngine;

/**
 * Add auto-numbering to an Eloquent model.
 *
 * Usage:
 *   use HasNumbering;
 *   protected array $numberingFields = ['invoice_number'];
 *
 * If $numberingFields is not defined, the trait auto-detects from the
 * numbering_sequences table based on model type and tenant column.
 */
trait HasNumbering
{
    /**
     * Track models with pending gap-free transactions.
     * Uses spl_object_id to avoid polluting model attributes.
     *
     * @var array<int, bool>
     */
    protected static array $pendingGapFreeTransactions = [];

    public static function bootHasNumbering(): void
    {
        static::creating(function (Model $model) {
            /** @var Model&HasNumbering $model */
            $engine = app(NumberingEngine::class);
            $fields = $model->resolveNumberingFields();

            foreach ($fields as $attribute) {
                // Skip if manually filled
                if (filled($model->getAttribute($attribute))) {
                    continue;
                }

                try {
                    $sequence = $engine->resolveSequence($model, $attribute);
                } catch (ModelNotFoundException) {
                    continue;
                }

                if ($sequence->is_gap_free) {
                    // For gap-free, wrap the entire creation in a transaction
                    // so the counter rolls back if the model save fails.
                    DB::beginTransaction();

                    try {
                        $number = $engine->generateGapFree($model, $sequence, $engine->buildScopeKey($sequence, $model));
                        $model->setAttribute($attribute, $number);
                        static::$pendingGapFreeTransactions[spl_object_id($model)] = true;
                    } catch (\Throwable $e) {
                        DB::rollBack();

                        throw $e;
                    }
                } else {
                    $model->setAttribute($attribute, $engine->generate($model, $sequence));
                }
            }
        });

        static::created(function (Model $model) {
            $objectId = spl_object_id($model);

            if (static::$pendingGapFreeTransactions[$objectId] ?? false) {
                DB::commit();
                unset(static::$pendingGapFreeTransactions[$objectId]);
            }
        });
    }

    /**
     * Resolve which attributes should be auto-numbered.
     *
     * @return array<string>
     */
    public function resolveNumberingFields(): array
    {
        if (property_exists($this, 'numberingFields') && ! empty($this->numberingFields)) {
            return $this->numberingFields;
        }

        // Auto-detect from database
        $query = NumberingSequence::where('model_type', $this->getMorphClass())
            ->where('is_active', true);

        if (config('filament-numbering-engine.multi_tenancy.enabled', false)) {
            $column = config('filament-numbering-engine.multi_tenancy.column', 'company_id');
            $query->when($this->{$column} ?? null, fn ($q, $id) => $q->where($column, $id));
        }

        return $query->pluck('attribute')->all();
    }

    /**
     * Generate a number for a specific attribute on demand.
     */
    public function generateNumber(?string $attribute = null): string
    {
        return app(NumberingEngine::class)->generate($this, attribute: $attribute);
    }

    /**
     * Preview the next number without consuming a counter value.
     */
    public function previewNextNumber(?string $attribute = null): string
    {
        return app(NumberingEngine::class)->preview($this, attribute: $attribute);
    }
}
