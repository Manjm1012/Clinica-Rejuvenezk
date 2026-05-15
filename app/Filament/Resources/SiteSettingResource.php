<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteSettingResource\Pages;
use App\Models\SiteSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Ajustes del Sitio';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('group')->required()->maxLength(50),
                Forms\Components\TextInput::make('key')->required()->maxLength(100),
                Forms\Components\TextInput::make('label')->maxLength(150),
                Forms\Components\Select::make('type')->options([
                    'string' => 'Texto corto',
                    'text' => 'Texto largo',
                    'boolean' => 'Booleano',
                    'integer' => 'Número',
                    'json' => 'JSON',
                    'image' => 'Imagen',
                ])->required()->live(),
                Forms\Components\FileUpload::make('value')
                    ->label('Imagen')
                    ->image()
                    ->disk('public')
                    ->directory('branding/banners')
                    ->visibility('public')
                    ->imageResizeMode('cover')
                    ->imageResizeTargetWidth(1600)
                    ->imageResizeTargetHeight(700)
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                    ])
                    ->maxSize(5120)
                    ->maxParallelUploads(1)
                    ->openable()
                    ->downloadable()
                    ->saveUploadedFileUsing(function (Forms\Components\BaseFileUpload $component, TemporaryUploadedFile $file): ?string {
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
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => $get('type') === 'image')
                    ->helperText('JPG, PNG o WebP. Máximo 5 MB. Se guarda en storage público.'),
                Forms\Components\Textarea::make('value')
                    ->columnSpanFull()
                    ->visible(fn (Get $get): bool => $get('type') !== 'image'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group')->badge()->sortable(),
                Tables\Columns\TextColumn::make('key')->searchable(),
                Tables\Columns\TextColumn::make('label')->searchable(),
                Tables\Columns\TextColumn::make('type')->badge(),
                Tables\Columns\TextColumn::make('value')->limit(40)->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group')
                    ->options(fn () => SiteSetting::query()->distinct()->pluck('group', 'group')->all()),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiteSettings::route('/'),
            'create' => Pages\CreateSiteSetting::route('/create'),
            'edit' => Pages\EditSiteSetting::route('/{record}/edit'),
        ];
    }
}
