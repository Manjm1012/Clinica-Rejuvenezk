<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\PatientResource\Pages;
use App\Filament\Doctor\Resources\PatientResource\RelationManagers;
use App\Filament\Resources\PatientResource as AdminPatientResource;
use App\Filament\Resources\PatientResource\RelationManagers\TreatmentsRelationManager;
use App\Models\Patient;
use App\Support\AdminClinic;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Pacientes en el panel médico: datos, información clínica e historia (consultas, fórmulas, tratamientos).
 * Todos los médicos de la clínica ven a todos sus pacientes (continuidad de la atención).
 */
class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Atención';

    protected static ?string $navigationLabel = 'Pacientes';

    protected static ?string $modelLabel = 'paciente';

    protected static ?string $pluralModelLabel = 'pacientes';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $slug = 'pacientes';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScope('clinic')
            ->where('patients.clinic_id', AdminClinic::idOrNone());
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'phone', 'document_number', 'email'];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos personales')->schema([
                Forms\Components\TextInput::make('name')->label('Nombre completo')->required()->maxLength(200)->columnSpanFull(),
                Forms\Components\Select::make('document_type')->label('Tipo de documento')->options(Patient::DOCUMENT_TYPES)->default('CC'),
                Forms\Components\TextInput::make('document_number')->label('Número de documento')->maxLength(30),
                Forms\Components\DatePicker::make('birth_date')->label('Fecha de nacimiento')->native(false)->displayFormat('d/m/Y')->maxDate(now()),
                Forms\Components\Select::make('gender')->label('Sexo')->options(Patient::GENDERS),
                Forms\Components\TextInput::make('phone')->label('Celular (WhatsApp)')->tel()->required()->maxLength(30)
                    ->rules([fn (?Patient $record): Closure => AdminPatientResource::uniqueInClinicRule('phone', $record)]),
                Forms\Components\TextInput::make('email')->label('Correo')->email()->maxLength(200)
                    ->rules([fn (?Patient $record): Closure => AdminPatientResource::uniqueInClinicRule('email', $record)]),
            ])->columns(2),

            Forms\Components\Section::make('Información clínica')
                ->description('Se guarda cifrada. Solo la ve el equipo médico.')
                ->schema([
                    Forms\Components\Textarea::make('allergies')->label('Alergias')->rows(2)
                        ->placeholder('Ej.: lidocaína, penicilina, látex. Escribe "Ninguna conocida" si aplica.'),
                    Forms\Components\Textarea::make('medical_history')->label('Antecedentes (patológicos, quirúrgicos, estéticos)')->rows(4),
                    Forms\Components\Textarea::make('current_medications')->label('Medicamentos actuales')->rows(2),
                ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Datos del paciente')->schema([
                Infolists\Components\TextEntry::make('name')->label('Nombre'),
                Infolists\Components\TextEntry::make('document')->label('Documento')
                    ->state(fn (Patient $record) => trim(($record->document_type ?? '').' '.($record->document_number ?? '')) ?: '—'),
                Infolists\Components\TextEntry::make('age')->label('Edad')
                    ->state(fn (Patient $record) => $record->birth_date ? $record->birth_date->age.' años ('.$record->birth_date->format('d/m/Y').')' : '—'),
                Infolists\Components\TextEntry::make('gender')->label('Sexo')
                    ->formatStateUsing(fn (?string $state) => Patient::GENDERS[$state] ?? '—')->placeholder('—'),
                Infolists\Components\TextEntry::make('phone')->label('Celular'),
                Infolists\Components\TextEntry::make('email')->label('Correo')->placeholder('—'),
            ])->columns(3),

            Infolists\Components\Section::make('Información clínica')->schema([
                Infolists\Components\TextEntry::make('allergies')->label('Alergias')
                    ->placeholder('Sin registrar')->color('danger')->weight('bold'),
                Infolists\Components\TextEntry::make('medical_history')->label('Antecedentes')->placeholder('Sin registrar')
                    ->formatStateUsing(fn (?string $state) => nl2br(e($state)))->html(),
                Infolists\Components\TextEntry::make('current_medications')->label('Medicamentos actuales')->placeholder('Sin registrar'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withMax([
                'consultations' => fn (Builder $q) => $q->withoutGlobalScope('clinic'),
            ], 'consulted_at'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('document_number')->label('Documento')->searchable()->placeholder('—'),
                Tables\Columns\TextColumn::make('phone')->label('Celular')->searchable(),
                Tables\Columns\TextColumn::make('birth_date')->label('Edad')
                    ->formatStateUsing(fn ($state) => $state ? $state->age.' años' : '—')->placeholder('—'),
                Tables\Columns\TextColumn::make('consultations_max_consulted_at')->label('Última consulta')
                    ->dateTime('d/m/Y')->placeholder('Sin consultas')->sortable(),
            ])
            ->defaultSort('name')
            ->actions([
                Tables\Actions\ViewAction::make()->label('Historia'),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ConsultationsRelationManager::class,
            RelationManagers\PrescriptionsRelationManager::class,
            TreatmentsRelationManager::class,
        ];
    }

    /** La historia clínica no se elimina desde el panel médico. */
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'view' => Pages\ViewPatient::route('/{record}'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
        ];
    }
}
