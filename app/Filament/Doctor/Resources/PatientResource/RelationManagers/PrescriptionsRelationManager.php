<?php

namespace App\Filament\Doctor\Resources\PatientResource\RelationManagers;

use App\Filament\Doctor\Resources\PrescriptionResource;
use App\Models\Prescription;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PrescriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'prescriptions';

    protected static ?string $title = 'Fórmulas e indicaciones';

    protected static ?string $modelLabel = 'fórmula';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScope('clinic')
                ->with(['doctor' => fn ($q) => $q->withoutGlobalScope('clinic')])
                ->withCount('items'))
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('code')->label('Número')->placeholder('Borrador'),
                Tables\Columns\TextColumn::make('doctor.name')->label('Médico'),
                Tables\Columns\TextColumn::make('items_count')->label('Medicamentos')->alignCenter(),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()
                    ->formatStateUsing(fn (string $state) => Prescription::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => PrescriptionResource::statusColor($state)),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('new')->label('Nueva fórmula')
                    ->url(fn () => PrescriptionResource::getUrl('create', ['patient' => $this->getOwnerRecord()->getKey()])),
            ])
            ->actions([
                Tables\Actions\Action::make('open')->label('Ver')->icon('heroicon-o-eye')
                    ->url(fn (Prescription $record) => PrescriptionResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
