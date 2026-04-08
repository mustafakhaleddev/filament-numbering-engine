<?php

namespace Wezlo\FilamentNumberingEngine\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Wezlo\FilamentNumberingEngine\Models\NumberingSequence;

class NumberGenerated
{
    use Dispatchable;

    public function __construct(
        public Model $model,
        public string $attribute,
        public string $generatedNumber,
        public NumberingSequence $sequence,
    ) {}
}
