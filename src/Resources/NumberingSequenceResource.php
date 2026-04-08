<?php

namespace Wezlo\FilamentNumberingEngine\Resources;

use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Wezlo\FilamentNumberingEngine\Concerns\HasNumbering;
use Wezlo\FilamentNumberingEngine\Enums\ResetFrequency;
use Wezlo\FilamentNumberingEngine\FilamentNumberingEnginePlugin;
use Wezlo\FilamentNumberingEngine\Models\NumberingSequence;
use Wezlo\FilamentNumberingEngine\Resources\NumberingSequenceResource\Pages\CreateNumberingSequence;
use Wezlo\FilamentNumberingEngine\Resources\NumberingSequenceResource\Pages\EditNumberingSequence;
use Wezlo\FilamentNumberingEngine\Resources\NumberingSequenceResource\Pages\ListNumberingSequences;

class NumberingSequenceResource extends Resource
{
    protected static ?string $model = NumberingSequence::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    public static function getModelLabel(): string
    {
        return __('filament-numbering-engine::numbering-engine.resource_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-numbering-engine::numbering-engine.resource_plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return FilamentNumberingEnginePlugin::resolveNavigationGroup();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('filament-numbering-engine::numbering-engine.form.sequence_details'))->schema([
                TextInput::make('name')
                    ->label(__('filament-numbering-engine::numbering-engine.form.name'))
                    ->required()
                    ->maxLength(255),
                Select::make('model_type')
                    ->label(__('filament-numbering-engine::numbering-engine.form.model_type'))
                    ->options(fn () => static::getNumberableModels())
                    ->required()
                    ->searchable(),
                TextInput::make('attribute')
                    ->label(__('filament-numbering-engine::numbering-engine.form.attribute'))
                    ->required()
                    ->maxLength(255)
                    ->helperText(__('filament-numbering-engine::numbering-engine.form.attribute_helper')),
                TextInput::make('pattern')
                    ->label(__('filament-numbering-engine::numbering-engine.form.pattern'))
                    ->required()
                    ->maxLength(255)
                    ->default(config('filament-numbering-engine.default_pattern'))
                    ->helperText(__('filament-numbering-engine::numbering-engine.form.pattern_helper')),
            ])->columns(2),

            Section::make(__('filament-numbering-engine::numbering-engine.form.formatting'))->schema([
                TextInput::make('prefix')
                    ->label(__('filament-numbering-engine::numbering-engine.form.prefix'))
                    ->maxLength(50),
                TextInput::make('suffix')
                    ->label(__('filament-numbering-engine::numbering-engine.form.suffix'))
                    ->maxLength(50),
                TextInput::make('initial_value')
                    ->label(__('filament-numbering-engine::numbering-engine.form.initial_value'))
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
            ])->columns(3),

            Section::make(__('filament-numbering-engine::numbering-engine.form.reset_settings'))->schema([
                Select::make('reset_frequency')
                    ->label(__('filament-numbering-engine::numbering-engine.form.reset_frequency'))
                    ->options(ResetFrequency::class)
                    ->default(config('filament-numbering-engine.default_reset_frequency'))
                    ->required()
                    ->live(),
                Select::make('fiscal_year_start_month')
                    ->label(__('filament-numbering-engine::numbering-engine.form.fiscal_year_start_month'))
                    ->options(fn () => collect(range(1, 12))->mapWithKeys(fn (int $m) => [
                        $m => Carbon::create()->month($m)->translatedFormat('F'),
                    ])->all())
                    ->default(config('filament-numbering-engine.default_fiscal_year_start_month'))
                    ->visible(fn (Get $get): bool => $get('reset_frequency') === ResetFrequency::Yearly->value),
                Toggle::make('is_gap_free')
                    ->label(__('filament-numbering-engine::numbering-engine.form.is_gap_free'))
                    ->default(config('filament-numbering-engine.default_gap_free'))
                    ->helperText(__('filament-numbering-engine::numbering-engine.form.is_gap_free_helper')),
                Toggle::make('is_active')
                    ->label(__('filament-numbering-engine::numbering-engine.form.is_active'))
                    ->default(true),
            ])->columns(2),

            Section::make(__('filament-numbering-engine::numbering-engine.form.custom_tokens'))->schema([
                KeyValue::make('custom_tokens')
                    ->label(__('filament-numbering-engine::numbering-engine.form.custom_tokens'))
                    ->keyLabel(__('filament-numbering-engine::numbering-engine.form.token_name'))
                    ->valueLabel(__('filament-numbering-engine::numbering-engine.form.token_resolver'))
                    ->helperText(__('filament-numbering-engine::numbering-engine.form.custom_tokens_helper')),
            ])->collapsible()->collapsed(),
        ]);
    }

    /**
     * Get models that use the HasNumbering trait from registered panel resources.
     *
     * @return array<string, string>
     */
    protected static function getNumberableModels(): array
    {
        $models = [];

        foreach (Filament::getCurrentOrDefaultPanel()->getResources() as $resource) {
            $modelClass = $resource::getModel();

            if (in_array(HasNumbering::class, class_uses_recursive($modelClass))) {
                $models[$modelClass] = $resource::getModelLabel();
            }
        }

        return $models;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament-numbering-engine::numbering-engine.table.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('model_type')
                    ->label(__('filament-numbering-engine::numbering-engine.table.model_type'))
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->sortable(),
                TextColumn::make('pattern')
                    ->label(__('filament-numbering-engine::numbering-engine.table.pattern'))
                    ->searchable(),
                TextColumn::make('reset_frequency')
                    ->label(__('filament-numbering-engine::numbering-engine.table.reset_frequency'))
                    ->badge(),
                IconColumn::make('is_gap_free')
                    ->label(__('filament-numbering-engine::numbering-engine.table.is_gap_free'))
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label(__('filament-numbering-engine::numbering-engine.table.is_active'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('filament-numbering-engine::numbering-engine.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNumberingSequences::route('/'),
            'create' => CreateNumberingSequence::route('/create'),
            'edit' => EditNumberingSequence::route('/{record}/edit'),
        ];
    }
}
