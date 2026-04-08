<?php

namespace Wezlo\FilamentNumberingEngine\TokenResolvers;

use Wezlo\FilamentNumberingEngine\Contracts\TokenResolver;

class DateTokenResolver implements TokenResolver
{
    public function resolve(string $token, ?string $argument, array $context): string
    {
        $now = now();

        return match ($token) {
            'year' => $argument === '2'
                ? $now->format('y')
                : $now->format('Y'),
            'month' => $now->format('m'),
            'day' => $now->format('d'),
            default => '',
        };
    }

    public function supports(string $token): bool
    {
        return in_array($token, ['year', 'month', 'day']);
    }
}
