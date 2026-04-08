<?php

namespace Wezlo\FilamentNumberingEngine\Enums;

use Filament\Support\Contracts\HasLabel;

enum ResetFrequency: string implements HasLabel
{
    case Never = 'never';
    case Yearly = 'yearly';
    case Monthly = 'monthly';
    case Daily = 'daily';

    public function getLabel(): string
    {
        return match ($this) {
            self::Never => __('filament-numbering-engine::numbering-engine.reset_frequency.never'),
            self::Yearly => __('filament-numbering-engine::numbering-engine.reset_frequency.yearly'),
            self::Monthly => __('filament-numbering-engine::numbering-engine.reset_frequency.monthly'),
            self::Daily => __('filament-numbering-engine::numbering-engine.reset_frequency.daily'),
        };
    }
}
