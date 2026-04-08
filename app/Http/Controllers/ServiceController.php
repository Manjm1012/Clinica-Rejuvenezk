<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\SiteSetting;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function show(Service $service): View
    {
        return view('service-show', [
            'service' => $service->load(['category', 'faqs', 'galleryItems']),
            'settings' => [
                'clinic_name' => SiteSetting::get('branding', 'clinic_name', 'Clínica Rejuvenezk'),
                'whatsapp_number' => SiteSetting::get('contact', 'whatsapp_number', ''),
                'phone' => SiteSetting::get('contact', 'phone', ''),
                'email' => SiteSetting::get('contact', 'email', ''),
            ],
            'relatedServices' => Service::query()
                ->where('is_active', true)
                ->whereKeyNot($service->getKey())
                ->orderBy('sort_order')
                ->limit(3)
                ->get(),
        ]);
    }
}
