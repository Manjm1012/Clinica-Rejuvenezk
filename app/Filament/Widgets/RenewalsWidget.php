<?php

namespace App\Filament\Widgets;

use App\Models\Clinic;
use App\Models\PatientTreatment;
use App\Support\AdminClinic;
use App\Support\Renewals;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

/**
 * Renovaciones próximas (≤ 30 días) y vencidas de la clínica del usuario.
 * Excluye 'no_aplica' y los tratamientos ya repetidos (solo el último por paciente+servicio).
 */
class RenewalsWidget extends TableWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Renovaciones próximas y vencidas';

    public static function canView(): bool
    {
        return AdminClinic::id() !== null;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => $this->renewalsQuery())
            ->columns([
                Tables\Columns\TextColumn::make('patient.name')->label('Paciente'),
                Tables\Columns\TextColumn::make('patient.phone')->label('Celular'),
                Tables\Columns\TextColumn::make('service.name')->label('Tratamiento')->placeholder('—'),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Fecha de renovación')
                    ->getStateUsing(fn (PatientTreatment $record) => $record->due_date)
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('renewal_status')
                    ->label('Estado')
                    ->getStateUsing(fn (PatientTreatment $record) => $record->renewal_status)
                    ->formatStateUsing(fn (string $state) => PatientTreatment::RENEWAL_LABELS[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state) => $state === 'vencido' ? 'danger' : 'warning'),
            ])
            ->actions([
                Tables\Actions\Action::make('whatsapp')
                    ->label('Escribir por WhatsApp')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->color('success')
                    ->url(fn (PatientTreatment $record) => Renewals::patientWhatsappUrl($record->patient?->phone, $this->messageFor($record)))
                    ->openUrlInNewTab()
                    ->visible(fn (PatientTreatment $record) => filled($record->patient?->phone)),
            ])
            ->emptyStateHeading('No hay renovaciones pendientes')
            ->emptyStateDescription('Aquí aparecerán los tratamientos que vencen en los próximos 30 días o ya vencieron.')
            ->paginated([10, 25, 50]);
    }

    protected function renewalsQuery(): Builder
    {
        $ids = Renewals::due(AdminClinic::idOrNone())->pluck('id')->all();

        $query = PatientTreatment::query()
            ->withoutGlobalScope('clinic')
            ->whereIn('id', $ids ?: [0])
            ->with([
                'patient' => fn ($q) => $q->withoutGlobalScope('clinic'),
                'service' => fn ($q) => $q->withoutGlobalScope('clinic'),
            ]);

        // Mismo orden que Renewals::due (por fecha de renovación).
        if ($ids) {
            $cases = collect($ids)->map(fn (int $id, int $position) => "WHEN {$id} THEN {$position}")->implode(' ');
            $query->orderByRaw("CASE id {$cases} END");
        }

        return $query;
    }

    protected function messageFor(PatientTreatment $record): string
    {
        $firstName = strtok((string) $record->patient?->name, ' ') ?: '';
        $clinicName = Clinic::query()->whereKey($record->clinic_id)->value('name') ?? '';
        $service = $record->service?->name ?? 'tu tratamiento';

        return trim("Hola {$firstName}, te escribimos de {$clinicName} para recordarte la renovación de {$service}. ¿Te ayudamos a agendar?");
    }
}
