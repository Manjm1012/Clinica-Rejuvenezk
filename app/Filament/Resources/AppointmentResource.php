<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Citas';
    protected static ?string $modelLabel = 'Cita';
    protected static ?string $pluralModelLabel = 'Citas';
    protected static ?string $navigationGroup = 'Clínica';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Detalle de la cita')
                ->schema([
                    Forms\Components\Select::make('patient_id')
                        ->label('Paciente')
                        ->relationship('patient', 'first_name')
                        ->getOptionLabelFromRecordUsing(fn (Patient $record) => "{$record->first_name} {$record->last_name}")
                        ->searchable()
                        ->preload()
                        ->required(),
                    Forms\Components\Select::make('doctor_id')
                        ->label('Médico')
                        ->relationship('doctor', 'full_name')
                        ->searchable()
                        ->preload(),
                    Forms\Components\DateTimePicker::make('scheduled_at')
                        ->label('Fecha y hora')
                        ->required()
                        ->native(false)
                        ->minutesStep(15),
                    Forms\Components\TextInput::make('duration_minutes')
                        ->label('Duración (min)')
                        ->numeric()
                        ->default(30)
                        ->minValue(10),
                    Forms\Components\Select::make('status')
                        ->label('Estado')
                        ->options(Appointment::$statuses)
                        ->default('pending')
                        ->required(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Notas')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Observaciones')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Fecha y hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($state, $record) => $record->patient?->first_name . ' ' . $record->patient?->last_name)
                    ->searchable(query: fn ($query, $search) => $query->whereHas('patient', fn ($q) => $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))),
                Tables\Columns\TextColumn::make('doctor.full_name')
                    ->label('Médico')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Appointment::$statuses[$state] ?? $state)
                    ->color(fn ($state) => Appointment::$statusColors[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('Min.')
                    ->suffix(' min')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('scheduled_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(Appointment::$statuses),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit'   => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
