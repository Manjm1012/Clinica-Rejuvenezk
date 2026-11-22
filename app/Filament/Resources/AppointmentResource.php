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

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'CRM & Leads';

    protected static ?string $navigationLabel = 'Citas';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('lead_id')
                    ->relationship('lead', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('patient_id')
                    ->label('Paciente registrado')
                    ->relationship('patient', 'name')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (?string $state, Forms\Set $set): void {
                        $patient = $state ? Patient::query()->find($state) : null;
                        if ($patient) {
                            $set('patient_name', $patient->name);
                            $set('patient_phone', $patient->phone);
                            $set('patient_email', $patient->email);
                        }
                    }),
                Forms\Components\Select::make('doctor_id')
                    ->label('Médico')
                    ->relationship('doctor', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('patient_name')
                    ->required(),
                Forms\Components\TextInput::make('patient_phone')
                    ->tel()
                    ->required(),
                Forms\Components\TextInput::make('patient_email')
                    ->email(),
                Forms\Components\DateTimePicker::make('appointment_at')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options(Appointment::STATUSES)
                    ->required()
                    ->default('pending'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('tayrai_appointment_id'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lead.name')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('patient_phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('patient_email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('doctor.name')
                    ->label('Médico')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('appointment_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->formatStateUsing(fn (?string $state) => Appointment::STATUSES[$state] ?? $state)
                    ->badge(),
                Tables\Columns\TextColumn::make('tayrai_appointment_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('appointment_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Estado')->options(Appointment::STATUSES),
                Tables\Filters\SelectFilter::make('doctor_id')->label('Médico')->relationship('doctor', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
