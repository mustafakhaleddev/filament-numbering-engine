<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Pattern
    |--------------------------------------------------------------------------
    |
    | The default pattern used when creating new numbering sequences.
    | Available tokens: {prefix}, {suffix}, {year}, {year:2}, {month},
    | {day}, {sequence}, {sequence:N}, {attribute:name}
    |
    */
    'default_pattern' => '{prefix}{year}-{sequence:4}{suffix}',

    /*
    |--------------------------------------------------------------------------
    | Default Reset Frequency
    |--------------------------------------------------------------------------
    |
    | How often the sequence counter resets.
    | Options: never, yearly, monthly, daily
    |
    */
    'default_reset_frequency' => 'yearly',

    /*
    |--------------------------------------------------------------------------
    | Default Fiscal Year Start Month
    |--------------------------------------------------------------------------
    |
    | The month number (1-12) when the fiscal year begins.
    | 1 = January, 4 = April, 7 = July, etc.
    |
    */
    'default_fiscal_year_start_month' => 1,

    /*
    |--------------------------------------------------------------------------
    | Default Gap-Free Mode
    |--------------------------------------------------------------------------
    |
    | Whether new sequences should use gap-free mode by default.
    | Gap-free mode uses database row locking to ensure no gaps in sequences.
    |
    */
    'default_gap_free' => false,

    /*
    |--------------------------------------------------------------------------
    | Navigation Group
    |--------------------------------------------------------------------------
    |
    | The navigation group under which the numbering sequences resource
    | appears in the Filament panel.
    |
    */
    'navigation_group' => 'Settings',

    /*
    |--------------------------------------------------------------------------
    | Multi-Tenancy
    |--------------------------------------------------------------------------
    |
    | Enable multi-tenancy to scope numbering sequences per tenant.
    | When enabled, the tenant_column is used on both the numbering_sequences
    | table and on models to isolate counters per tenant.
    |
    | Set tenant_column to match your application's tenant foreign key
    | (e.g. 'company_id', 'team_id', 'organization_id').
    |
    | Set column_type to match your tenant model's primary key type.
    | Use 'uuid' for UUID-based models, or 'unsignedBigInteger' for
    | auto-incrementing integer keys.
    |
    */
    'multi_tenancy' => [
        'enabled' => false,
        'column' => 'company_id',
        'column_type' => 'unsignedBigInteger',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Token Resolvers
    |--------------------------------------------------------------------------
    |
    | Register custom token resolver classes that implement
    | Wezlo\FilamentNumberingEngine\Contracts\TokenResolver.
    |
    */
    'custom_resolvers' => [],

];
