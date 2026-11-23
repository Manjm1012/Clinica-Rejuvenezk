<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AdminOnly;
use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use App\Support\AdminClinic;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Auditoría de accesos y cambios a datos clínicos (solo lectura). */
class AuditLogResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Auditoría';

    protected static ?string $modelLabel = 'registro';

    protected static ?string $pluralModelLabel = 'auditoría';

    protected static ?int $navigationSort = 9;

    protected static ?string $slug = 'auditoria';

    private const TYPES = [
        'App\\Models\\Patient' => 'Paciente',
        'App\\Models\\Consultation' => 'Consulta',
        'App\\Models\\Prescription' => 'Fórmula',
    ];

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('clinic_id', AdminClinic::idOrNone());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i:s')->sortable(),
                Tables\Columns\TextColumn::make('actor_name')->label('Quién')->placeholder('Sistema')->searchable()
                    ->description(fn (AuditLog $record) => match ($record->actor_type) {
                        'patient' => 'Paciente (portal)',
                        'user' => 'Equipo',
                        default => null,
                    }),
                Tables\Columns\TextColumn::make('action')->label('Acción')->badge()
                    ->formatStateUsing(fn (string $state) => AuditLog::ACTIONS[$state] ?? $state),
                Tables\Columns\TextColumn::make('auditable_type')->label('Registro')
                    ->formatStateUsing(fn (string $state, AuditLog $record) => (self::TYPES[$state] ?? class_basename($state)).' #'.$record->auditable_id),
                Tables\Columns\TextColumn::make('changes')->label('Campos')->placeholder('—')
                    ->formatStateUsing(fn ($state) => is_array($state) ? implode(', ', $state) : $state)->toggleable(),
                Tables\Columns\TextColumn::make('ip')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('action')->label('Acción')->options(AuditLog::ACTIONS),
                Tables\Filters\SelectFilter::make('auditable_type')->label('Tipo')->options(self::TYPES),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
        ];
    }
}
