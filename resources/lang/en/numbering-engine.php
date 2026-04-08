<?php

return [

    // Navigation
    'resource_label' => 'Numbering Sequence',
    'resource_plural' => 'Numbering Sequences',

    // Reset frequency
    'reset_frequency' => [
        'never' => 'Never',
        'yearly' => 'Yearly',
        'monthly' => 'Monthly',
        'daily' => 'Daily',
    ],

    // Form
    'form' => [
        'sequence_details' => 'Sequence Details',
        'name' => 'Name',
        'model_type' => 'Model',
        'attribute' => 'Attribute',
        'attribute_helper' => 'The model attribute to auto-fill (e.g., invoice_number)',
        'pattern' => 'Pattern',
        'pattern_helper' => 'Tokens: {sequence:4}, {year}, {year:2}, {month}, {day}, {prefix}, {suffix}, {attribute:name}',
        'formatting' => 'Formatting',
        'prefix' => 'Prefix',
        'suffix' => 'Suffix',
        'initial_value' => 'Initial Value',
        'reset_settings' => 'Reset Settings',
        'reset_frequency' => 'Reset Frequency',
        'fiscal_year_start_month' => 'Fiscal Year Start Month',
        'is_gap_free' => 'Gap-Free Mode',
        'is_gap_free_helper' => 'Uses database locking to guarantee no gaps in sequence numbers',
        'is_active' => 'Active',
        'custom_tokens' => 'Custom Tokens',
        'token_name' => 'Token Name',
        'token_resolver' => 'Resolver',
        'custom_tokens_helper' => 'Map custom token names to resolvers (e.g., branch => attribute:branch.code)',
    ],

    // Table
    'table' => [
        'name' => 'Name',
        'model_type' => 'Model',
        'pattern' => 'Pattern',
        'reset_frequency' => 'Reset',
        'is_gap_free' => 'Gap-Free',
        'is_active' => 'Active',
        'created_at' => 'Created At',
    ],

];
