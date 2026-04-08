<?php

namespace Wezlo\FilamentNumberingEngine\TokenResolvers;

use Illuminate\Database\Eloquent\Model;
use Wezlo\FilamentNumberingEngine\Contracts\TokenResolver;

class AttributeTokenResolver implements TokenResolver
{
    public function resolve(string $token, ?string $argument, array $context): string
    {
        /** @var Model $model */
        $model = $context['model'];

        if ($argument === null) {
            return '';
        }

        return (string) data_get($model, $argument, '');
    }

    public function supports(string $token): bool
    {
        return $token === 'attribute';
    }
}
