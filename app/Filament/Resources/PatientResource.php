<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Filament\Resources\PatientResource\RelationManagers\TreatmentsRelationManager;
use App\Models\Patient;
use App\Models\PatientTreatment;
use App\Support\AdminClinic;
use App\Support\Renewals;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Pacientes';

    protected static ?string $navigationLabel = 'Pacientes';

    protected static ?string $modelLabel = 'paciente';

    protected static ?string $pluralModelLabel = 'pacientes';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'pacientes';

    /** Solo pacientes de la clínica del usuario (sin depender del global scope). */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScope('clinic')
            ->where('patients.clinic_id', AdminClinic::idOrNone());
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del paciente')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre completo')
                        ->required()
                        ->maxLength(200),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('document_type')
                            ->label('Tipo de documento')
                            ->options(Patient::DOCUMENT_TYPES),
                        Forms\Components\TextInput::make('document_number')
                            ->label('Número de documento')
                            ->maxLength(30),
                    ])->columnSpan(1),
                    Forms\Components\TextInput::make('phone')
                        ->label('Celular (WhatsApp)')
                        ->tel()
                        ->required()
                        ->maxLength(30)
                        ->helperText('Se guarda solo con números; el indicativo 57 se quita automáticamente.')
                        ->rules([fn (?Patient $record): Closure => static::uniqueInClinicRule('phone', $record)]),
                    Forms\Components\TextInput::make('email')
                        ->label('Correo')
                        ->email()
                        ->maxLength(200)
                        ->rules([fn (?Patient $record): Closure => static::uniqueInClinicRule('email', $record)]),
                    Forms\Components\DatePicker::make('birth_date')
                        ->label('Fecha de nacimiento')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->maxDate(now()),
                    Forms\Components\Select::make('gender')
                        ->label('Sexo')
                        ->options(Patient::GENDERS),
                ])->columns(2),

            Forms\Components\Section::make('Acceso y consentimientos')
                ->schema([
                    Forms\Components\TextInput::make('password')
                        ->label(fn (string $operation) => $operation === 'create' ? 'Contraseña del portal (opcional)' : 'Nueva contraseña del portal')
                        ->password()
                        ->revealable()
                        ->minLength(8)
                        ->maxLength(255)
                        ->dehydrated(fn (?string $state) => filled($state))
                        ->helperText('Déjala vacía para no cambiarla. Asignarla activa el acceso del paciente a "Mi cuenta".'),
                    Forms\Components\Toggle::make('marketing_consent')
                        ->label('Acepta recibir promociones por WhatsApp/correo')
                        ->helperText('Solo con autorización expresa del paciente.'),
                    Forms\Components\Placeholder::make('marketing_consent_at')
                        ->label('Fecha del consentimiento de marketing')
                        ->content(fn (?Patient $record) => static::localDateTime($record?->marketing_consent_at, $record) ?? '—'),
                    Forms\Components\Placeholder::make('data_policy_accepted_at')
                        ->label('Aceptó la política de datos')
                        ->content(fn (?Patient $record) => $record?->data_policy_accepted_at
                            ? static::localDateTime($record->data_policy_accepted_at, $record).' (versión '.($record->data_policy_version ?? '—').')'
                            : 'No registrada (paciente cargado por la clínica)'),
                ])->columns(2),
        ]);
    }

    /** Fecha y hora en la zona de la clínica (la app guarda en UTC). */
    protected static function localDateTime(?\DateTimeInterface $date, ?Patient $record): ?string
    {
        if (! $date) {
            return null;
        }

        $timezone = PatientTreatment::timezoneForClinic($record?->clinic_id ?? AdminClinic::id());

        return Carbon::instance($date)->setTimezone($timezone)->format('d/m/Y H:i');
    }

    /** Único por clínica comparando el valor ya normalizado (teléfono en dígitos, correo en minúsculas). */
    public static function uniqueInClinicRule(string $column, ?Patient $record): Closure
    {
        return function (string $attribute, $value, Closure $fail) use ($column, $record): void {
            $normalized = $column === 'phone' ? Patient::normalizePhone($value) : Patient::normalizeEmail($value);

            if ($normalized === null || $normalized === '') {
                return;
            }

            $exists = Patient::query()
                ->withoutGlobalScope('clinic')
                ->where('clinic_id', $record?->clinic_id ?? AdminClinic::idOrNone())
                ->where($column, $normalized)
                ->when($record, fn (Builder $query) => $query->whereKeyNot($record->getKey()))
                ->exists();

            if ($exists) {
                $fail($column === 'phone' ? 'Ya existe un paciente con ese celular.' : 'Ya existe un paciente con ese correo.');
            }
        };
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount([
                'treatments' => fn (Builder $q) => $q->withoutGlobalScope('clinic'),
            ]))
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label('Celular')->searchable(),
                Tables\Columns\TextColumn::make('document_number')->label('Documento')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('email')->label('Correo')->searchable()->toggleable(),
                Tables\Columns\IconColumn::make('marketing_consent')->label('Marketing')->boolean()->alignCenter(),
                Tables\Columns\TextColumn::make('treatments_count')->label('Tratamientos')->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Registro')
                    ->date('d/m/Y', PatientTreatment::timezoneForClinic(AdminClinic::id()))->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('marketing_consent')
                    ->label('Con consentimiento de marketing')
                    ->query(fn (Builder $query) => $query->where('marketing_consent', true))
                    ->toggle(),
                Tables\Filters\Filter::make('overdue')
                    ->label('Con renovaciones vencidas')
                    ->query(fn (Builder $query) => $query->whereIn(
                        'patients.id',
                        Renewals::due(AdminClinic::idOrNone(), ['vencido'])->pluck('patient_id')->unique()->all() ?: [0],
                    ))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            // Sin borrado masivo: un paciente con historia clínica no se puede eliminar.
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            TreatmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
        ];
    }
}
