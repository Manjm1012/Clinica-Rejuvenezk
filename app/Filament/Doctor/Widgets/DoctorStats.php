<?php

namespace App\Filament\Doctor\Widgets;

use App\Filament\Doctor\Resources\ConsultationResource;
use App\Filament\Doctor\Resources\ServiceRequestResource;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\PatientTreatment;
use App\Models\ServiceRequest;
use App\Support\AdminClinic;
use App\Support\CurrentDoctor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class DoctorStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $clinicId = AdminClinic::idOrNone();
        $doctorId = CurrentDoctor::idOrNone();
        $timezone = PatientTreatment::timezoneForClinic($clinicId);
        $todayStart = Carbon::now($timezone)->startOfDay()->utc();
        $todayEnd = Carbon::now($timezone)->endOfDay()->utc();

        $appointmentsToday = Appointment::query()->withoutGlobalScope('clinic')
            ->where('clinic_id', $clinicId)
            ->where('doctor_id', $doctorId)
            ->whereBetween('appointment_at', [$todayStart, $todayEnd])
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        $drafts = Consultation::query()->withoutGlobalScope('clinic')
            ->where('clinic_id', $clinicId)
            ->where('doctor_id', $doctorId)
            ->where('status', Consultation::STATUS_DRAFT)
            ->count();

        $pendingRequests = ServiceRequest::query()->withoutGlobalScope('clinic')
            ->where('clinic_id', $clinicId)
            ->where('status', 'pending')
            ->count();

        return [
            Stat::make('Mis citas de hoy', $appointmentsToday)
                ->icon('heroicon-o-calendar-days'),
            Stat::make('Consultas sin firmar', $drafts)
                ->description($drafts ? 'Fírmalas para cerrar la historia' : 'Todo al día')
                ->color($drafts ? 'warning' : 'success')
                ->icon('heroicon-o-pencil-square')
                ->url(ConsultationResource::getUrl('index')),
            Stat::make('Solicitudes de renovación', $pendingRequests)
                ->description('Pendientes de agendar')
                ->color($pendingRequests ? 'warning' : 'gray')
                ->icon('heroicon-o-inbox-arrow-down')
                ->url(ServiceRequestResource::getUrl('index')),
        ];
    }
}
