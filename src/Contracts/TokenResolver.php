<?php

namespace Wezlo\FilamentNumberingEngine\Contracts;

use Illuminate\Database\Eloquent\Model;
use Wezlo\FilamentNumberingEngine\Models\NumberingSequence;

interface TokenResolver
{
    /**
     * Resolve a token to its string value.
     *
     * @param  array{model: Model, sequence_value: int, sequence: NumberingSequence}  $context
     */
    public function resolve(string $token, ?string $argument, array $context): string;

    /**
     * Whether this resolver handles the given token name.
     */
    public function supports(string $token): bool;
}
