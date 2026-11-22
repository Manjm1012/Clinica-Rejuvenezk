<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\PublicSettings;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('services-index', [
            'settings' => PublicSettings::all(),
            'serviceCategories' => ServiceCategory::query()
                ->where('is_active', true)
                ->whereHas('services', fn ($query) => $query->where('is_active', true))
                ->with([
                    'services' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                ])
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    public function show(Service $service): View
    {
        abort_unless($service->is_active, 404);

        return view('service-show', [
            'service' => $service->load([
                'category',
                'faqs' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                'galleryItems' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
            ]),
            'settings' => PublicSettings::all(),
            'relatedServices' => Service::query()
                ->where('is_active', true)
                ->whereKeyNot($service->getKey())
                ->orderBy('sort_order')
                ->limit(3)
                ->get(),
        ]);
    }
}
