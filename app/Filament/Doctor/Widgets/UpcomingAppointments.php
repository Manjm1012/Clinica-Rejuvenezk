<?php

namespace App\Filament\Doctor\Widgets;

use App\Filament\Doctor\Resources\PatientResource;
use App\Models\Appointment;
use App\Support\AdminClinic;
use App\Support\CurrentDoctor;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class UpcomingAppointments extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Mis próximas citas (7 días)';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Appointment::query()->withoutGlobalScope('clinic')
                ->where('clinic_id', AdminClinic::idOrNone())
                ->where('doctor_id', CurrentDoctor::idOrNone())
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereBetween('appointment_at', [now()->startOfDay(), now()->addDays(7)->endOfDay()])
                ->with(['service' => fn ($q) => $q->withoutGlobalScope('clinic')])
                ->orderBy('appointment_at'))
            ->columns([
                Tables\Columns\TextColumn::make('appointment_at')->label('Fecha')->dateTime('D d/m H:i'),
                Tables\Columns\TextColumn::make('patient_name')->label('Paciente'),
                Tables\Columns\TextColumn::make('service.name')->label('Tratamiento')->placeholder('—'),
                Tables\Columns\TextColumn::make('patient_phone')->label('Celular'),
            ])
            ->actions([
                Tables\Actions\Action::make('history')->label('Historia')->icon('heroicon-o-folder-open')
                    ->visible(fn (Appointment $record) => $record->patient_id !== null)
                    ->url(fn (Appointment $record) => $record->patient_id ? PatientResource::getUrl('view', ['record' => $record->patient_id]) : null),
            ])
            ->emptyStateHeading('Sin citas en los próximos 7 días')
            ->paginated(false);
    }
}
