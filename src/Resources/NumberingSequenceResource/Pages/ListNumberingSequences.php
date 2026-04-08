<?php

namespace Wezlo\FilamentNumberingEngine\Resources\NumberingSequenceResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Wezlo\FilamentNumberingEngine\Resources\NumberingSequenceResource;

class ListNumberingSequences extends ListRecords
{
    protected static string $resource = NumberingSequenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
