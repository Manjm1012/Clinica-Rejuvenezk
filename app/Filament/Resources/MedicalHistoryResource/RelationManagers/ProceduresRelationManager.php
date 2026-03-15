<?php

namespace App\Filament\Resources\MedicalHistoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ProceduresRelationManager extends RelationManager
{
    protected static string $relationship = 'procedures';
    protected static ?string $title = 'Procedimientos realizados';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Procedimiento')
                ->required()
                ->maxLength(180),
            Forms\Components\TextInput::make('amount')
                ->label('Valor (COP)')
                ->numeric()
                ->prefix('$'),
            Forms\Components\DateTimePicker::make('performed_at')
                ->label('Fecha de realización')
                ->native(false),
            Forms\Components\Textarea::make('notes')
                ->label('Notas del procedimiento')
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Procedimiento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Valor')
                    ->money('COP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('performed_at')
                    ->label('Realizado el')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
