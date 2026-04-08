<?php

namespace Wezlo\FilamentNumberingEngine\Resources\NumberingSequenceResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Wezlo\FilamentNumberingEngine\Resources\NumberingSequenceResource;

class CreateNumberingSequence extends CreateRecord
{
    protected static string $resource = NumberingSequenceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (config('filament-numbering-engine.multi_tenancy.enabled', false)) {
            $column = config('filament-numbering-engine.multi_tenancy.column', 'company_id');
            $data[$column] = auth()->user()?->{$column};
        }

        return $data;
    }
}
