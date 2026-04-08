<?php

namespace Wezlo\FilamentNumberingEngine\TokenResolvers;

use Wezlo\FilamentNumberingEngine\Contracts\TokenResolver;

class SequenceTokenResolver implements TokenResolver
{
    public function resolve(string $token, ?string $argument, array $context): string
    {
        $value = $context['sequence_value'] ?? 0;
        $padWidth = (int) ($argument ?: 4);

        return str_pad((string) $value, $padWidth, '0', STR_PAD_LEFT);
    }

    public function supports(string $token): bool
    {
        return $token === 'sequence';
    }
}
