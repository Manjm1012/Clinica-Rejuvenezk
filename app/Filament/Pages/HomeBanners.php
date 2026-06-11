<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class HomeBanners extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Banners Home';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Banners Home';

    protected static string $view = 'filament.pages.home-banners';

    public ?string $banner1Path = null;

    public ?string $banner2Path = null;

    public ?string $banner3Path = null;

    public function mount(): void
    {
        $this->banner1Path = SiteSetting::get('branding', 'home_banner_1_image', '');
        $this->banner2Path = SiteSetting::get('branding', 'home_banner_2_image', '');
        $this->banner3Path = SiteSetting::get('branding', 'home_banner_3_image', '');
    }

    public function getBannerUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}
