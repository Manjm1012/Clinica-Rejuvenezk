<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalHistoryResource\Pages;
use App\Filament\Resources\MedicalHistoryResource\RelationManagers;
use App\Models\MedicalHistory;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MedicalHistoryResource extends Resource
{
    protected static ?string $model = MedicalHistory::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Historias Clínicas';
    protected static ?string $modelLabel = 'Historia Clínica';
    protected static ?string $pluralModelLabel = 'Historias Clínicas';
    protected static ?string $navigationGroup = 'Clínica';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información de la consulta')
                ->schema([
                    Forms\Components\Select::make('patient_id')
                        ->label('Paciente')
                        ->relationship('patient', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn (Patient $record) => "{$record->first_name} {$record->last_name}")
                        ->searchable()
                        ->preload()
                        ->required(),
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
                        ->maxLength(255),
                ])
                ->columns(2),

            Forms\Components\Section::make('Diagnóstico y tratamiento')
                ->schema([
                    Forms\Components\Textarea::make('diagnosis')
                        ->label('Diagnóstico')
                        ->rows(4),
                    Forms\Components\Textarea::make('treatment_plan')
                        ->label('Plan de tratamiento')
                        ->rows(4),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('consulted_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($state, $record) => $record->patient?->first_name . ' ' . $record->patient?->last_name)
                    ->searchable(query: fn ($query, $search) => $query->whereHas('patient', fn ($q) => $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))),
                Tables\Columns\TextColumn::make('doctor.full_name')
                    ->label('Médico')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reason_for_consultation')
                    ->label('Motivo')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('procedures_count')
                    ->label('Procedimientos')
                    ->counts('procedures')
                    ->sortable(),
            ])
            ->defaultSort('consulted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('doctor_id')
                    ->label('Médico')
                    ->relationship('doctor', 'full_name'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProceduresRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMedicalHistories::route('/'),
            'create' => Pages\CreateMedicalHistory::route('/create'),
            'edit'   => Pages\EditMedicalHistory::route('/{record}/edit'),
        ];
    }
}
