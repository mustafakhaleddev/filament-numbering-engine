<?php

namespace Wezlo\FilamentNumberingEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NumberingCounter extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'current_value' => 'integer',
        ];
    }

    public function sequence(): BelongsTo
    {
        return $this->belongsTo(NumberingSequence::class, 'numbering_sequence_id');
    }
}
