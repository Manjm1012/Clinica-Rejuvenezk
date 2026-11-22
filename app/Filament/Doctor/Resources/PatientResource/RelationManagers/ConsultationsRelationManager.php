<?php

namespace App\Filament\Doctor\Resources\PatientResource\RelationManagers;

use App\Filament\Doctor\Resources\ConsultationResource;
use App\Models\Consultation;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConsultationsRelationManager extends RelationManager
{
    protected static string $relationship = 'consultations';

    protected static ?string $title = 'Consultas';

    protected static ?string $modelLabel = 'consulta';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScope('clinic')
                ->with(['service' => fn ($q) => $q->withoutGlobalScope('clinic'), 'doctor' => fn ($q) => $q->withoutGlobalScope('clinic')]))
            ->columns([
                Tables\Columns\TextColumn::make('consulted_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('service.name')->label('Servicio')->placeholder('—'),
                Tables\Columns\TextColumn::make('doctor.name')->label('Médico'),
                Tables\Columns\TextColumn::make('status')->label('Estado')->badge()
                    ->formatStateUsing(fn (string $state) => Consultation::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => $state === Consultation::STATUS_SIGNED ? 'success' : 'warning'),
            ])
            ->defaultSort('consulted_at', 'desc')
            ->headerActions([
                Tables\Actions\Action::make('new')->label('Nueva consulta')
                    ->url(fn () => ConsultationResource::getUrl('create', ['patient' => $this->getOwnerRecord()->getKey()])),
            ])
            ->actions([
                Tables\Actions\Action::make('open')->label('Ver')->icon('heroicon-o-eye')
                    ->url(fn (Consultation $record) => ConsultationResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
