<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GalleryItemResource\Pages;
use App\Models\GalleryItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GalleryItemResource extends Resource
{
    protected static ?string $model = GalleryItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Contenido del Sitio';
    protected static ?string $navigationLabel = 'Galería';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('title'),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('before_image_path')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('gallery/before')
                    ->imageEditor(),
                Forms\Components\FileUpload::make('after_image_path')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('gallery/after')
                    ->imageEditor(),
                Forms\Components\FileUpload::make('image_path')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('gallery/single')
                    ->imageEditor(),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'before_after' => 'Antes / Después',
                        'single' => 'Imagen única',
                        'video' => 'Video',
                    ]),
                Forms\Components\TextInput::make('video_url'),
                Forms\Components\Toggle::make('is_featured')
                    ->required(),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service.name')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('before_image_path')->label('Antes')->disk('public'),
                Tables\Columns\ImageColumn::make('after_image_path')->label('Después')->disk('public'),
                Tables\Columns\ImageColumn::make('image_path')->label('Imagen')->disk('public'),
                Tables\Columns\TextColumn::make('type')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => Pages\ListGalleryItems::route('/'),
            'create' => Pages\CreateGalleryItem::route('/create'),
            'edit' => Pages\EditGalleryItem::route('/{record}/edit'),
        ];
    }
}
