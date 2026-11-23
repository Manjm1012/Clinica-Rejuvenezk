<?php

namespace App\Filament\Resources\PatientResource\Pages;

use App\Filament\Resources\PatientResource;
use App\Support\AdminClinic;
use App\Support\PatientCsvExport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPatients extends ListRecords
{
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('exportCsv')
                ->label('Exportar CSV (con consentimiento)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => PatientCsvExport::download(AdminClinic::idOrNone())),
            Actions\CreateAction::make(),
        ];
    }
}
