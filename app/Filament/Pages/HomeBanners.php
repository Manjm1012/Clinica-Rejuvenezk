<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class HomeBanners extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $navigationLabel = 'Banners Home';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Banners Home';

    protected static string $view = 'filament.pages.home-banners';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'home_banner_1_image' => SiteSetting::get('branding', 'home_banner_1_image', ''),
            'home_banner_2_image' => SiteSetting::get('branding', 'home_banner_2_image', ''),
            'home_banner_3_image' => SiteSetting::get('branding', 'home_banner_3_image', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Carrusel de banners')
                    ->description('Sube 3 imágenes para el carrusel de inicio. Recomendado: 1600x700 px o relación 16:7.')
                    ->schema([
                        FileUpload::make('home_banner_1_image')
                            ->label('Banner 1')
                            ->image()
                            ->disk('public')
                            ->directory('branding/banners')
                            ->visibility('public')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(5120)
                            ->maxParallelUploads(1)
                            ->openable()
                            ->downloadable()
                            ->saveUploadedFileUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file): ?string {
                                $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
                                $path = trim('branding/banners/' . Str::ulid() . '.' . $extension, '/');
                                $stream = fopen($file->getRealPath(), 'r');

                                if ($stream === false) {
                                    return null;
                                }

                                try {
                                    Storage::disk('public')->put($path, $stream, [
                                        'visibility' => 'public',
                                    ]);
                                } finally {
                                    fclose($stream);
                                }

                                return Storage::disk('public')->exists($path) ? $path : null;
                            })
                            ->helperText('JPG, PNG o WebP. Máximo 5 MB. Se sube de un archivo a la vez para mayor estabilidad.'),
                        FileUpload::make('home_banner_2_image')
                            ->label('Banner 2')
                            ->image()
                            ->disk('public')
                            ->directory('branding/banners')
                            ->visibility('public')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(5120)
                            ->maxParallelUploads(1)
                            ->openable()
                            ->downloadable()
                            ->saveUploadedFileUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file): ?string {
                                $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
                                $path = trim('branding/banners/' . Str::ulid() . '.' . $extension, '/');
                                $stream = fopen($file->getRealPath(), 'r');

                                if ($stream === false) {
                                    return null;
                                }

                                try {
                                    Storage::disk('public')->put($path, $stream, [
                                        'visibility' => 'public',
                                    ]);
                                } finally {
                                    fclose($stream);
                                }

                                return Storage::disk('public')->exists($path) ? $path : null;
                            }),
                        FileUpload::make('home_banner_3_image')
                            ->label('Banner 3')
                            ->image()
                            ->disk('public')
                            ->directory('branding/banners')
                            ->visibility('public')
                            ->acceptedFileTypes([
                                'image/jpeg',
                                'image/png',
                                'image/webp',
                            ])
                            ->maxSize(5120)
                            ->maxParallelUploads(1)
                            ->openable()
                            ->downloadable()
                            ->saveUploadedFileUsing(function (BaseFileUpload $component, TemporaryUploadedFile $file): ?string {
                                $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
                                $path = trim('branding/banners/' . Str::ulid() . '.' . $extension, '/');
                                $stream = fopen($file->getRealPath(), 'r');

                                if ($stream === false) {
                                    return null;
                                }

                                try {
                                    Storage::disk('public')->put($path, $stream, [
                                        'visibility' => 'public',
                                    ]);
                                } finally {
                                    fclose($stream);
                                }

                                return Storage::disk('public')->exists($path) ? $path : null;
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $values = $this->form->getState();

        SiteSetting::set('branding', 'home_banner_1_image', $values['home_banner_1_image'] ?? '', 'image', 'Imagen banner home 1');
        SiteSetting::set('branding', 'home_banner_2_image', $values['home_banner_2_image'] ?? '', 'image', 'Imagen banner home 2');
        SiteSetting::set('branding', 'home_banner_3_image', $values['home_banner_3_image'] ?? '', 'image', 'Imagen banner home 3');

        Notification::make()
            ->title('Banners actualizados')
            ->success()
            ->send();
    }
}
