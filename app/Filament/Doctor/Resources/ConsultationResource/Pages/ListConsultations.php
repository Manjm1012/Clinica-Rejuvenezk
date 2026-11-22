<?php

namespace App\Filament\Doctor\Resources\ConsultationResource\Pages;

use App\Filament\Doctor\Resources\ConsultationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListConsultations extends ListRecords
{
    protected static string $resource = ConsultationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Nueva consulta')];
    }
}
