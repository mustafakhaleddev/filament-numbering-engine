<?php

namespace Wezlo\FilamentNumberingEngine\Services;

use Illuminate\Database\Eloquent\Model;
use Wezlo\FilamentNumberingEngine\Contracts\TokenResolver;
use Wezlo\FilamentNumberingEngine\Models\NumberingSequence;
use Wezlo\FilamentNumberingEngine\TokenResolvers\AttributeTokenResolver;
use Wezlo\FilamentNumberingEngine\TokenResolvers\DateTokenResolver;
use Wezlo\FilamentNumberingEngine\TokenResolvers\SequenceTokenResolver;

class PatternParser
{
    /** @var array<TokenResolver> */
    protected array $resolvers = [];

    public function __construct()
    {
        $this->resolvers = [
            new DateTokenResolver,
            new SequenceTokenResolver,
            new AttributeTokenResolver,
        ];
    }

    public function registerResolver(TokenResolver $resolver): void
    {
        array_unshift($this->resolvers, $resolver);
    }

    /**
     * Parse a pattern string, replacing all {token} and {token:arg} placeholders.
     *
     * @param  array{model: Model, sequence_value: int, sequence: NumberingSequence}  $context
     */
    public function parse(string $pattern, array $context): string
    {
        /** @var NumberingSequence $sequence */
        $sequence = $context['sequence'];
        $customTokens = $sequence->custom_tokens ?? [];

        return (string) preg_replace_callback('/\{([a-zA-Z_]+)(?::([^}]*))?\}/', function (array $matches) use ($context, $customTokens) {
            $token = $matches[1];
            $argument = $matches[2] ?? null;

            // Handle built-in prefix/suffix tokens
            if ($token === 'prefix') {
                return $context['sequence']->prefix ?? '';
            }

            if ($token === 'suffix') {
                return $context['sequence']->suffix ?? '';
            }

            // Check custom token aliases (e.g., "branch" => "attribute:branch.code")
            if (isset($customTokens[$token])) {
                $aliasedValue = $customTokens[$token];

                if (str_contains($aliasedValue, ':')) {
                    [$token, $argument] = explode(':', $aliasedValue, 2);
                } else {
                    $token = $aliasedValue;
                }
            }

            // Delegate to registered resolvers
            foreach ($this->resolvers as $resolver) {
                if ($resolver->supports($token)) {
                    return $resolver->resolve($token, $argument, $context);
                }
            }

            // Unknown token — return as-is
            return $matches[0];
        }, $pattern);
    }
}
