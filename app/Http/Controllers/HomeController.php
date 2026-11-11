<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\GalleryItem;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Stat;
use App\Models\Technology;
use App\Models\Testimonial;
use App\Support\PublicSettings;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', $this->pageData());
    }

    public function about(): View
    {
        return view('about', $this->pageData());
    }

    private function pageData(): array
    {
        return [
            'settings' => PublicSettings::all(),
            'clinic' => Clinic::query()->where('is_active', true)->orderBy('id')->first(),
            'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'serviceCategories' => ServiceCategory::query()
                ->where('is_active', true)
                ->whereHas('services', fn ($query) => $query->where('is_active', true))
                ->with([
                    'services' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order'),
                ])
                ->orderBy('sort_order')
                ->get(),
            'featuredServices' => Service::query()->where('is_active', true)->where('is_featured', true)->orderBy('sort_order')->limit(6)->get(),
            'doctor' => Doctor::query()->where('is_active', true)->orderBy('sort_order')->first(),
            'galleryItems' => GalleryItem::query()
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNotNull('image_path')
                        ->orWhereNotNull('before_image_path')
                        ->orWhereNotNull('after_image_path')
                        ->orWhereNotNull('video_url');
                })
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->limit(12)
                ->get(),
            'stats' => Stat::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'technologies' => Technology::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'testimonials' => Testimonial::query()->where('is_active', true)->orderByDesc('is_featured')->orderBy('sort_order')->limit(8)->get(),
        ];
    }
}
