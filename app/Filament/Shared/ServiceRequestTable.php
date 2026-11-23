<?php

namespace App\Filament\Shared;

use App\Actions\ScheduleServiceRequest;
use App\Models\Doctor;
use App\Models\ServiceRequest;
use App\Support\AdminClinic;
use App\Support\Renewals;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Tabla de solicitudes de renovación, compartida por el panel admin (recepción) y el panel médico.
 */
class ServiceRequestTable
{
    public static function query(Builder $query): Builder
    {
        return $query->withoutGlobalScope('clinic')
            ->where('service_requests.clinic_id', AdminClinic::idOrNone())
            ->with([
                'patient' => fn ($q) => $q->withoutGlobalScope('clinic'),
                'service' => fn ($q) => $q->withoutGlobalScope('clinic'),
                'doctor' => fn ($q) => $q->withoutGlobalScope('clinic'),
                'appointment' => fn ($q) => $q->withoutGlobalScope('clinic'),
            ]);
    }

    public static function statusColor(?string $state): string
    {
        return match ($state) {
            'pending' => 'warning',
            'scheduled' => 'info',
            'completed' => 'success',
            default => 'gray',
        };
    }

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Solicitada')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('patient.name')->label('Paciente')->searchable()
                    ->description(fn (ServiceRequest $record) => $record->patient?->phone),
                Tables\Columns\TextColumn::make('service.name')->label('Tratamiento')->placeholder('—'),
                Tables\Columns\TextColumn::make('preferred_date')->label('Prefiere')
                    ->formatStateUsing(fn ($state, ServiceRequest $record) => ($state ? $state->format('d/m/Y') : 'Sin fecha')
                        .($record->preferred_time ? ' · '.(ServiceRequest::TIMES[$record->preferred_time] ?? $record->preferred_time) : '')),
                Tables\Columns\TextColumn::make('appointment.appointment_at')->label('Cita')->dateTime('d/m/Y H:i')->placeholder('—'),
                Tables\Columns\TextColumn::make('doctor.name')->label('Médico')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()
                    ->formatStateUsing(fn (string $state) => ServiceRequest::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => static::statusColor($state)),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('Estado')->options(ServiceRequest::STATUSES)->default('pending'),
            ])
            ->actions([
                Tables\Actions\Action::make('schedule')
                    ->label(fn (ServiceRequest $record) => $record->status === 'scheduled' ? 'Reprogramar' : 'Agendar')
                    ->icon('heroicon-o-calendar-days')
                    ->color('primary')
                    ->visible(fn (ServiceRequest $record) => $record->isOpen())
                    ->fillForm(fn (ServiceRequest $record) => [
                        'appointment_at' => $record->appointment?->appointment_at
                            ?? Carbon::parse(($record->preferred_date ?? now()->addDay())->toDateString().' '.($record->preferred_time === 'tarde' ? '15:00' : '09:00')),
                        'doctor_id' => $record->doctor_id ?? $record->treatment?->doctor_id,
                    ])
                    ->form([
                        Forms\Components\DateTimePicker::make('appointment_at')->label('Fecha y hora de la cita')
                            ->required()->native(false)->seconds(false)->minDate(now()->startOfDay()),
                        Forms\Components\Select::make('doctor_id')->label('Médico')
                            ->options(fn () => Doctor::query()->withoutGlobalScope('clinic')
                                ->where('clinic_id', AdminClinic::idOrNone())->where('is_active', true)
                                ->orderBy('name')->pluck('name', 'id')),
                        Forms\Components\Textarea::make('notes')->label('Nota para la cita')->rows(2),
                    ])
                    ->action(function (ServiceRequest $record, array $data): void {
                        app(ScheduleServiceRequest::class)->handle(
                            $record,
                            Carbon::parse($data['appointment_at']),
                            $data['doctor_id'] ?? null,
                            auth()->user(),
                            $data['notes'] ?? null,
                        );

                        Notification::make()->title('Cita agendada')->success()->send();
                    }),

                Tables\Actions\Action::make('whatsapp')
                    ->label('WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('gray')
                    ->url(fn (ServiceRequest $record) => Renewals::patientWhatsappUrl(
                        $record->patient?->phone,
                        'Hola '.$record->patient?->name.', te escribimos por tu solicitud de renovación'
                            .($record->service ? ' de '.$record->service->name : '').'.',
                    ), shouldOpenInNewTab: true)
                    ->visible(fn (ServiceRequest $record) => filled($record->patient?->phone)),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('complete')
                        ->label('Marcar atendida')
                        ->icon('heroicon-o-check')
                        ->visible(fn (ServiceRequest $record) => $record->status === 'scheduled')
                        ->requiresConfirmation()
                        ->action(function (ServiceRequest $record): void {
                            $record->status = 'completed';
                            $record->handled_by = auth()->id();
                            $record->save();
                        }),
                    Tables\Actions\Action::make('cancel')
                        ->label('Cancelar solicitud')
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->visible(fn (ServiceRequest $record) => $record->isOpen())
                        ->form([
                            Forms\Components\Textarea::make('staff_notes')->label('Motivo (interno)')->rows(2),
                        ])
                        ->action(function (ServiceRequest $record, array $data): void {
                            $record->status = 'cancelled';
                            $record->staff_notes = $data['staff_notes'] ?? null;
                            $record->handled_by = auth()->id();
                            $record->save();
                        }),
                ]),
            ])
            ->bulkActions([]);
    }
}
