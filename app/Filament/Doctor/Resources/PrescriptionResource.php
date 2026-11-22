<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\PrescriptionResource\Pages;
use App\Models\Consultation;
use App\Models\Prescription;
use App\Support\AdminClinic;
use App\Support\ClinicRule;
use App\Support\CurrentDoctor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Fórmulas: indicaciones y cuidados posteriores + medicamentos estéticos.
 * Borrador editable por su autor -> Emitida (la ve el paciente) -> Anulada.
 */
class PrescriptionResource extends Resource
{
    protected static ?string $model = Prescription::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Atención';

    protected static ?string $navigationLabel = 'Fórmulas';

    protected static ?string $modelLabel = 'fórmula';

    protected static ?string $pluralModelLabel = 'fórmulas';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'formulas';

    public static function statusColor(?string $state): string
    {
        return match ($state) {
            Prescription::STATUS_ISSUED => 'success',
            Prescription::STATUS_ANNULLED => 'danger',
            default => 'warning',
        };
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScope('clinic')
            ->where('prescriptions.clinic_id', AdminClinic::idOrNone())
            ->with([
                'patient' => fn ($q) => $q->withoutGlobalScope('clinic'),
                'doctor' => fn ($q) => $q->withoutGlobalScope('clinic'),
            ]);
    }

    public static function form(Form $form): Form
    {
        $templates = config('clinical.care_templates', []);

        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Select::make('patient_id')
                    ->rule(fn () => ClinicRule::exists('patients'))
                    ->label('Paciente')
                    ->options(fn () => ConsultationResource::patientOptions())
                    ->searchable()
                    ->required()
                    ->live()
                    ->default(fn () => request()->integer('patient') ?: null)
                    ->disabledOn('edit')
                    ->dehydrated(fn (string $operation) => $operation === 'create'),
                Forms\Components\Select::make('consultation_id')
                    ->label('Consulta relacionada (opcional)')
                    ->options(fn (Get $get, ?Prescription $record) => Consultation::query()->withoutGlobalScope('clinic')
                        ->where('clinic_id', AdminClinic::idOrNone())
                        ->where('patient_id', $record?->patient_id ?? $get('patient_id') ?? 0)
                        ->orderByDesc('consulted_at')
                        ->get(['id', 'consulted_at'])
                        ->mapWithKeys(fn (Consultation $c) => [$c->id => 'Consulta del '.$c->consulted_at->format('d/m/Y H:i')])
                        ->all())
                    ->default(fn () => request()->integer('consultation') ?: null),
                Forms\Components\DatePicker::make('valid_until')
                    ->label('Válida hasta')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->default(fn () => now()->addDays((int) config('clinical.prescription_valid_days', 30))),
            ])->columns(3),

            Forms\Components\Section::make('Medicamentos y productos')
                ->description('Nombre genérico o producto, concentración, forma, vía, dosis, frecuencia y duración.')
                ->schema([
                    Forms\Components\Repeater::make('items')
                        ->relationship()
                        ->label('')
                        ->orderColumn('sort_order')
                        ->addActionLabel('Agregar medicamento')
                        ->defaultItems(0)
                        ->collapsible()
                        ->itemLabel(fn (array $state) => $state['medication'] ?? null)
                        ->schema([
                            Forms\Components\TextInput::make('medication')->label('Medicamento / producto')->required()->maxLength(200)->columnSpan(2),
                            Forms\Components\TextInput::make('concentration')->label('Concentración')->maxLength(100)->placeholder('Ej.: 0,05 %'),
                            Forms\Components\TextInput::make('dosage_form')->label('Forma farmacéutica')->maxLength(100)
                                ->datalist(['Crema', 'Gel', 'Loción', 'Sérum', 'Ungüento', 'Tableta', 'Cápsula', 'Solución', 'Protector solar']),
                            Forms\Components\TextInput::make('route')->label('Vía')->maxLength(100)->datalist(['Tópica', 'Oral', 'Subcutánea']),
                            Forms\Components\TextInput::make('dose')->label('Dosis')->maxLength(100)->placeholder('Ej.: capa fina'),
                            Forms\Components\TextInput::make('frequency')->label('Frecuencia')->maxLength(150)->placeholder('Ej.: cada noche'),
                            Forms\Components\TextInput::make('duration')->label('Duración')->maxLength(100)->placeholder('Ej.: 30 días'),
                            Forms\Components\TextInput::make('quantity')->label('Cantidad')->maxLength(100)->placeholder('Ej.: 1 tubo x 30 g'),
                            Forms\Components\Textarea::make('instructions')->label('Indicaciones específicas')->rows(2)->columnSpanFull(),
                        ])
                        ->columns(3),
                ]),

            Forms\Components\Section::make('Indicaciones y cuidados posteriores')->schema([
                Forms\Components\Select::make('template')
                    ->label('Cargar plantilla')
                    ->options(collect($templates)->mapWithKeys(fn (array $t, string $key) => [$key => $t['label']])->all())
                    ->placeholder('Elige una plantilla para empezar (opcional)')
                    ->dehydrated(false)
                    ->live()
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get) use ($templates): void {
                        if (! $state || ! isset($templates[$state])) {
                            return;
                        }

                        $current = trim((string) $get('care_instructions'));
                        $set('care_instructions', trim($current."\n\n".$templates[$state]['text']));
                        $set('template', null);
                    }),
                Forms\Components\Textarea::make('care_instructions')
                    ->label('Indicaciones para el paciente')
                    ->rows(10)
                    ->helperText('El paciente verá este texto en su portal y en la fórmula impresa.'),
                Forms\Components\Textarea::make('diagnosis')
                    ->label('Diagnóstico (opcional)')
                    ->rows(2),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $text = fn (?string $state) => $state ? nl2br(e($state)) : null;

        return $infolist->schema([
            Infolists\Components\Section::make()->schema([
                Infolists\Components\TextEntry::make('patient.name')->label('Paciente')
                    ->url(fn (Prescription $record) => PatientResource::getUrl('view', ['record' => $record->patient_id])),
                Infolists\Components\TextEntry::make('doctor.name')->label('Médico'),
                Infolists\Components\TextEntry::make('status')->label('Estado')->badge()
                    ->formatStateUsing(fn (string $state) => Prescription::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => static::statusColor($state)),
                Infolists\Components\TextEntry::make('code')->label('Número')->placeholder('Se asigna al emitir'),
                Infolists\Components\TextEntry::make('issued_at')->label('Emitida')->dateTime('d/m/Y H:i')->placeholder('—'),
                Infolists\Components\TextEntry::make('valid_until')->label('Válida hasta')->date('d/m/Y')->placeholder('—'),
                Infolists\Components\TextEntry::make('annulled_reason')->label('Motivo de anulación')
                    ->visible(fn (Prescription $record) => $record->status === Prescription::STATUS_ANNULLED)->color('danger')->columnSpanFull(),
            ])->columns(3),

            Infolists\Components\Section::make('Medicamentos y productos')->schema([
                Infolists\Components\RepeatableEntry::make('items')->label('')->schema([
                    Infolists\Components\TextEntry::make('medication')->label('Medicamento')->weight('bold')->columnSpan(2),
                    Infolists\Components\TextEntry::make('concentration')->label('Concentración')->placeholder('—'),
                    Infolists\Components\TextEntry::make('dosage_form')->label('Forma')->placeholder('—'),
                    Infolists\Components\TextEntry::make('route')->label('Vía')->placeholder('—'),
                    Infolists\Components\TextEntry::make('dose')->label('Dosis')->placeholder('—'),
                    Infolists\Components\TextEntry::make('frequency')->label('Frecuencia')->placeholder('—'),
                    Infolists\Components\TextEntry::make('duration')->label('Duración')->placeholder('—'),
                    Infolists\Components\TextEntry::make('quantity')->label('Cantidad')->placeholder('—'),
                    Infolists\Components\TextEntry::make('instructions')->label('Indicaciones')->placeholder('—')->columnSpanFull(),
                ])->columns(4),
            ])->visible(fn (Prescription $record) => $record->items()->exists()),

            Infolists\Components\Section::make('Indicaciones y cuidados posteriores')->schema([
                Infolists\Components\TextEntry::make('care_instructions')->label('')->formatStateUsing($text)->html()->placeholder('—'),
                Infolists\Components\TextEntry::make('diagnosis')->label('Diagnóstico')->formatStateUsing($text)->html()->placeholder('—'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('items'))
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('patient.name')->label('Paciente')->searchable(),
                Tables\Columns\TextColumn::make('code')->label('Número')->placeholder('Borrador')->searchable(),
                Tables\Columns\TextColumn::make('items_count')->label('Medicamentos')->alignCenter(),
                Tables\Columns\TextColumn::make('doctor.name')->label('Médico')->toggleable(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()
                    ->formatStateUsing(fn (string $state) => Prescription::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => static::statusColor($state)),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('mine')->label('Solo mis fórmulas')->toggle()->default()
                    ->query(fn (Builder $query) => $query->where('doctor_id', CurrentDoctor::idOrNone())),
                Tables\Filters\SelectFilter::make('status')->label('Estado')->options(Prescription::STATUSES),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function canEdit(Model $record): bool
    {
        return $record->isDraft() && (int) $record->doctor_id === CurrentDoctor::idOrNone();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canEdit($record);
    }

    public static function canCreate(): bool
    {
        return CurrentDoctor::id() !== null;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrescriptions::route('/'),
            'create' => Pages\CreatePrescription::route('/create'),
            'view' => Pages\ViewPrescription::route('/{record}'),
            'edit' => Pages\EditPrescription::route('/{record}/edit'),
        ];
    }
}
