<?php

namespace App\Filament\Doctor\Resources;

use App\Filament\Doctor\Resources\ConsultationResource\Pages;
use App\Models\Consultation;
use App\Models\Patient;
use App\Models\Service;
use App\Support\AdminClinic;
use App\Support\CurrentDoctor;
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
 * Consultas médicas. El médico solo edita sus borradores; al firmar quedan cerradas.
 */
class ConsultationResource extends Resource
{
    protected static ?string $model = Consultation::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Atención';

    protected static ?string $navigationLabel = 'Consultas';

    protected static ?string $modelLabel = 'consulta';

    protected static ?string $pluralModelLabel = 'consultas';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'consultas';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScope('clinic')
            ->where('consultations.clinic_id', AdminClinic::idOrNone())
            ->with([
                'patient' => fn ($q) => $q->withoutGlobalScope('clinic'),
                'doctor' => fn ($q) => $q->withoutGlobalScope('clinic'),
                'service' => fn ($q) => $q->withoutGlobalScope('clinic'),
            ]);
    }

    public static function patientOptions(): array
    {
        return Patient::query()->withoutGlobalScope('clinic')
            ->where('clinic_id', AdminClinic::idOrNone())
            ->orderBy('name')
            ->get(['id', 'name', 'document_number'])
            ->mapWithKeys(fn (Patient $p) => [$p->id => $p->name.($p->document_number ? ' · '.$p->document_number : '')])
            ->all();
    }

    public static function serviceOptions(): array
    {
        return Service::query()->withoutGlobalScope('clinic')
            ->where('clinic_id', AdminClinic::idOrNone())
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Select::make('patient_id')
                    ->label('Paciente')
                    ->options(fn () => static::patientOptions())
                    ->searchable()
                    ->required()
                    ->default(fn () => request()->integer('patient') ?: null)
                    ->disabledOn('edit')
                    ->dehydrated(fn (string $operation) => $operation === 'create'),
                Forms\Components\Select::make('service_id')
                    ->label('Servicio / procedimiento')
                    ->options(fn () => static::serviceOptions())
                    ->searchable(),
                Forms\Components\DateTimePicker::make('consulted_at')
                    ->label('Fecha y hora')
                    ->required()
                    ->native(false)
                    ->seconds(false)
                    ->displayFormat('d/m/Y H:i')
                    ->default(now()),
            ])->columns(3),

            Forms\Components\Section::make('Registro clínico')
                ->description('Se guarda cifrado. Al firmar, la consulta ya no se puede modificar.')
                ->schema([
                    Forms\Components\Textarea::make('reason')->label('Motivo de consulta')->rows(2)->required(),
                    Forms\Components\Textarea::make('findings')->label('Examen físico / hallazgos')->rows(4),
                    Forms\Components\Textarea::make('diagnosis')->label('Impresión diagnóstica')->rows(2),
                    Forms\Components\Textarea::make('plan')->label('Plan / procedimiento realizado')->rows(4)
                        ->helperText('Producto, lote, dosis o unidades, zonas tratadas y técnica, si aplica.'),
                ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        $text = fn (?string $state) => $state ? nl2br(e($state)) : null;

        return $infolist->schema([
            Infolists\Components\Section::make()->schema([
                Infolists\Components\TextEntry::make('patient.name')->label('Paciente')
                    ->url(fn (Consultation $record) => PatientResource::getUrl('view', ['record' => $record->patient_id])),
                Infolists\Components\TextEntry::make('doctor.name')->label('Médico'),
                Infolists\Components\TextEntry::make('consulted_at')->label('Fecha')->dateTime('d/m/Y H:i'),
                Infolists\Components\TextEntry::make('service.name')->label('Servicio')->placeholder('—'),
                Infolists\Components\TextEntry::make('status')->label('Estado')->badge()
                    ->formatStateUsing(fn (string $state) => Consultation::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => $state === Consultation::STATUS_SIGNED ? 'success' : 'warning'),
                Infolists\Components\TextEntry::make('signed_at')->label('Firmada el')->dateTime('d/m/Y H:i')->placeholder('Sin firmar'),
            ])->columns(3),

            Infolists\Components\Section::make('Registro clínico')->schema([
                Infolists\Components\TextEntry::make('reason')->label('Motivo de consulta')->formatStateUsing($text)->html()->placeholder('—'),
                Infolists\Components\TextEntry::make('findings')->label('Examen físico / hallazgos')->formatStateUsing($text)->html()->placeholder('—'),
                Infolists\Components\TextEntry::make('diagnosis')->label('Impresión diagnóstica')->formatStateUsing($text)->html()->placeholder('—'),
                Infolists\Components\TextEntry::make('plan')->label('Plan / procedimiento')->formatStateUsing($text)->html()->placeholder('—'),
            ]),

            Infolists\Components\Section::make('Notas aclaratorias')
                ->visible(fn (Consultation $record) => $record->addenda()->exists())
                ->schema([
                    Infolists\Components\RepeatableEntry::make('addenda')->label('')->schema([
                        Infolists\Components\TextEntry::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i'),
                        Infolists\Components\TextEntry::make('author.name')->label('Autor')->placeholder('—'),
                        Infolists\Components\TextEntry::make('body')->label('Nota')->formatStateUsing($text)->html()->columnSpanFull(),
                    ])->columns(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('consulted_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('patient.name')->label('Paciente')->searchable(),
                Tables\Columns\TextColumn::make('service.name')->label('Servicio')->placeholder('—'),
                Tables\Columns\TextColumn::make('doctor.name')->label('Médico')->toggleable(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()
                    ->formatStateUsing(fn (string $state) => Consultation::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => $state === Consultation::STATUS_SIGNED ? 'success' : 'warning'),
            ])
            ->defaultSort('consulted_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('mine')->label('Solo mis consultas')->toggle()->default()
                    ->query(fn (Builder $query) => $query->where('doctor_id', CurrentDoctor::idOrNone())),
                Tables\Filters\SelectFilter::make('status')->label('Estado')->options(Consultation::STATUSES),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    /** Solo el médico autor edita, y solo mientras es borrador. */
    public static function canEdit(Model $record): bool
    {
        return ! $record->isSigned() && (int) $record->doctor_id === CurrentDoctor::idOrNone();
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
            'index' => Pages\ListConsultations::route('/'),
            'create' => Pages\CreateConsultation::route('/create'),
            'view' => Pages\ViewConsultation::route('/{record}'),
            'edit' => Pages\EditConsultation::route('/{record}/edit'),
        ];
    }
}
