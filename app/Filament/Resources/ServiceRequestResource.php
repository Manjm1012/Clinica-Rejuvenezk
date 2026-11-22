<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceRequestResource\Pages;
use App\Filament\Shared\ServiceRequestTable;
use App\Models\ServiceRequest;
use App\Support\AdminClinic;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** Solicitudes de renovación enviadas por los pacientes desde su portal. */
class ServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Pacientes';

    protected static ?string $navigationLabel = 'Solicitudes de renovación';

    protected static ?string $modelLabel = 'solicitud';

    protected static ?string $pluralModelLabel = 'solicitudes';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'solicitudes';

    public static function getEloquentQuery(): Builder
    {
        return ServiceRequestTable::query(parent::getEloquentQuery());
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ServiceRequest::query()->withoutGlobalScope('clinic')
            ->where('clinic_id', AdminClinic::idOrNone())
            ->where('status', 'pending')
            ->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return ServiceRequestTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceRequests::route('/'),
        ];
    }
}
