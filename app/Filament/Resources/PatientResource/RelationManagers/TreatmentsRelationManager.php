<?php

namespace App\Filament\Resources\PatientResource\RelationManagers;

use App\Models\Doctor;
use App\Models\PatientTreatment;
use App\Models\Service;
use App\Support\ClinicRule;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TreatmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'treatments';

    protected static ?string $title = 'Tratamientos';

    protected static ?string $modelLabel = 'tratamiento';

    protected static ?string $pluralModelLabel = 'tratamientos';

    public function form(Form $form): Form
    {
        $clinicId = $this->getOwnerRecord()->clinic_id;

        return $form->schema([
            Forms\Components\Select::make('service_id')
                ->rule(fn () => ClinicRule::exists('services', $clinicId))
                ->label('Servicio')
                ->options(fn () => Service::query()->withoutGlobalScope('clinic')
                    ->where('clinic_id', $clinicId)->orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->helperText('La renovación se calcula con "Renovar cada (días)" del servicio.'),
            Forms\Components\Select::make('doctor_id')
                ->rule(fn () => ClinicRule::exists('doctors', $clinicId))
                ->label('Doctor')
                ->options(fn () => Doctor::query()->withoutGlobalScope('clinic')
                    ->where('clinic_id', $clinicId)->orderBy('name')->pluck('name', 'id'))
                ->searchable(),
            Forms\Components\DatePicker::make('performed_at')
                ->label('Fecha del procedimiento')
                ->required()
                ->native(false)
                ->displayFormat('d/m/Y')
                ->default(fn () => PatientTreatment::todayForClinic($clinicId)),
            Forms\Components\DatePicker::make('next_due_at')
                ->label('Próxima renovación (opcional)')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->helperText('Déjala vacía para calcularla con el intervalo del servicio.'),
            Forms\Components\Textarea::make('notes')
                ->label('Notas visibles para el paciente')
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\Textarea::make('internal_notes')
                ->label('Notas internas (solo administración, el paciente nunca las ve)')
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScope('clinic')->with([
                'service' => fn ($q) => $q->withoutGlobalScope('clinic'),
                'doctor' => fn ($q) => $q->withoutGlobalScope('clinic'),
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('performed_at')->label('Fecha')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('service.name')->label('Servicio')->placeholder('—'),
                Tables\Columns\TextColumn::make('doctor.name')->label('Doctor')->placeholder('—')->toggleable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Renovar')
                    ->getStateUsing(fn (PatientTreatment $record) => $record->due_date)
                    ->date('d/m/Y')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('renewal_status')
                    ->label('Estado')
                    ->getStateUsing(fn (PatientTreatment $record) => $record->renewal_status)
                    ->formatStateUsing(fn (string $state) => PatientTreatment::RENEWAL_LABELS[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'vigente' => 'success',
                        'proxima' => 'warning',
                        'vencido' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('notes')->label('Notas visibles')->limit(40)->toggleable(),
            ])
            ->defaultSort('performed_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    // clinic_id y patient_id se fijan desde el paciente (no son asignables en masa).
                    ->using(function (array $data): Model {
                        $owner = $this->getOwnerRecord();

                        $treatment = new PatientTreatment($data);
                        $treatment->patient_id = $owner->getKey();
                        $treatment->clinic_id = $owner->clinic_id;
                        $treatment->save();

                        return $treatment;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
