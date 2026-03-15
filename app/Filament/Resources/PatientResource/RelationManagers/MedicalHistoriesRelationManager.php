<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MedicalHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'medicalHistories';
    protected static ?string $title = 'Historias Clínicas';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('doctor_id')
                ->label('Médico tratante')
                ->relationship('doctor', 'full_name')
                ->searchable()
                ->preload(),
            Forms\Components\DateTimePicker::make('consulted_at')
                ->label('Fecha de consulta')
                ->default(now())
                ->required()
                ->native(false),
            Forms\Components\TextInput::make('reason_for_consultation')
                ->label('Motivo de consulta')
                ->maxLength(255)
                ->columnSpanFull(),
            Forms\Components\Textarea::make('diagnosis')
                ->label('Diagnóstico')
                ->rows(4)
                ->columnSpanFull(),
            Forms\Components\Textarea::make('treatment_plan')
                ->label('Plan de tratamiento')
                ->rows(4)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason_for_consultation')
            ->columns([
                Tables\Columns\TextColumn::make('consulted_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('doctor.full_name')
                    ->label('Médico'),
                Tables\Columns\TextColumn::make('reason_for_consultation')
                    ->label('Motivo')
                    ->limit(60),
                Tables\Columns\TextColumn::make('procedures_count')
                    ->label('Procedimientos')
                    ->counts('procedures'),
            ])
            ->defaultSort('consulted_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
