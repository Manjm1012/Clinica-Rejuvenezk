<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Stat;
use App\Models\Testimonial;
use App\Support\CurrentClinic;
use App\Support\PublicSettings;
use App\View\HomePage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(CurrentClinic $currentClinic): View
    {
        $data = [
            'settings' => PublicSettings::all(),
            'clinic' => $currentClinic->clinic(),
            'services' => Service::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'slug']),
            'serviceCategories' => $this->activeCategories(),
            'featuredServices' => Service::query()->where('is_active', true)->where('is_featured', true)->orderBy('sort_order')->limit(6)->get(),
            'doctor' => Doctor::query()->where('is_active', true)->orderBy('sort_order')->first(),
            'galleryItems' => $this->galleryItems(12),
            'stats' => Stat::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'testimonials' => Testimonial::query()->where('is_active', true)->orderByDesc('is_featured')->orderBy('sort_order')->limit(8)->get(),
        ];

        return view('home', $data + ['page' => HomePage::build($data)]);
    }

    public function about(): View
    {
        return view('about', [
            'settings' => PublicSettings::all(),
            'doctor' => Doctor::query()->where('is_active', true)->orderBy('sort_order')->first(),
        ]);
    }

    /** Resultados: antes/después, galería y videos completos + testimonios reales. */
    public function results(): View
    {
        $items = $this->galleryItems(60);
        $settings = PublicSettings::all();

        return view('results', [
            'settings' => $settings,
            'beforeAfter' => HomePage::beforeAfter($items, 24),
            'gallery' => HomePage::gallery($items, 24),
            'videos' => HomePage::videos($items, 12),
            'testimonials' => Testimonial::query()->where('is_active', true)->orderByDesc('is_featured')->orderBy('sort_order')->get(),
            'page' => ['texts' => HomePage::texts($settings)],
        ]);
    }

    /** Equipo médico: todos los médicos activos con su formación. */
    public function team(): View
    {
        return view('team', [
            'settings' => PublicSettings::all(),
            'doctors' => Doctor::query()
                ->where('is_active', true)
                ->with('credentials')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    /** Preguntas frecuentes agrupadas por tratamiento. */
    public function faq(): View
    {
        $faqs = Faq::query()
            ->where('is_active', true)
            ->with(['service' => fn ($q) => $q->select(['id', 'name', 'slug', 'is_active'])])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn (Faq $faq) => ! $faq->service || $faq->service->is_active);

        return view('faq', [
            'settings' => PublicSettings::all(),
            'groups' => $faqs->groupBy(fn (Faq $faq) => $faq->service?->name ?? 'Generales')
                ->sortBy(fn ($items, $name) => $name === 'Generales' ? '' : $name),
        ]);
    }

    /** Contacto y agenda: formulario de leads + canales tomados de los ajustes públicos. */
    public function contact(Request $request): View
    {
        $services = Service::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'slug']);
        $slug = $request->query('servicio');

        return view('contact', [
            'settings' => PublicSettings::all(),
            'services' => $services,
            // ?servicio=slug (desde una tarjeta) preselecciona el tratamiento; un valor ya enviado manda.
            'selectedServiceId' => is_string($slug) ? $services->firstWhere('slug', $slug)?->id : null,
        ]);
    }

    public function sitemap(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('services.index'), 'priority' => '0.9'],
            ['loc' => route('results'), 'priority' => '0.8'],
            ['loc' => route('team'), 'priority' => '0.8'],
            ['loc' => route('about'), 'priority' => '0.7'],
            ['loc' => route('contact'), 'priority' => '0.8'],
            ['loc' => route('faq'), 'priority' => '0.6'],
            ['loc' => route('legal.data-policy'), 'priority' => '0.3'],
        ])->merge(
            Service::query()->where('is_active', true)->orderBy('sort_order')->get(['slug', 'updated_at'])
                ->map(fn (Service $service) => [
                    'loc' => route('services.show', $service),
                    'lastmod' => $service->updated_at?->toAtomString(),
                    'priority' => '0.8',
                ])
        );

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function activeCategories()
    {
        return ServiceCategory::query()
            ->where('is_active', true)
            ->whereHas('services', fn ($query) => $query->where('is_active', true))
            ->with(['services' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }

    private function galleryItems(int $limit)
    {
        return GalleryItem::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNotNull('image_path')
                ->orWhereNotNull('before_image_path')
                ->orWhereNotNull('after_image_path')
                ->orWhereNotNull('video_url'))
            ->with('service:id,name')
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }
}
