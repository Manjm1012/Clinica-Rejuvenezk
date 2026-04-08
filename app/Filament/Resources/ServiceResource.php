<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use App\Models\ServiceCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Procedimientos';
    protected static ?string $navigationLabel = 'Servicios / Procedimientos';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Servicio';
    protected static ?string $pluralModelLabel = 'Servicios';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información General')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del procedimiento')
                        ->required()
                        ->maxLength(200)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Forms\Set $set, ?string $state) =>
                            $set('slug', Str::slug($state ?? ''))
                        ),
                    Forms\Components\TextInput::make('slug')
                        ->label('URL amigable (slug)')
                        ->required()
                        ->unique(Service::class, 'slug', ignoreRecord: true)
                        ->maxLength(220),
                    Forms\Components\Select::make('service_category_id')
                        ->label('Categoría')
                        ->options(ServiceCategory::pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->live(),
                    Forms\Components\Select::make('meta.subcategory')
                        ->label('Subcategoría')
                        ->options(function (Get $get): array {
                            $categoryId = $get('service_category_id');
                            $category = $categoryId ? ServiceCategory::find($categoryId) : null;

                            return match ($category?->slug) {
                                'facial' => [
                                    'Rellenos faciales' => 'Rellenos faciales',
                                    'Bioestimuladores de colágeno' => 'Bioestimuladores de colágeno',
                                    'Armonización facial con ácido hialurónico' => 'Armonización facial con ácido hialurónico',
                                    'Revitalización facial' => 'Revitalización facial',
                                    'Procedimientos mínimamente invasivos' => 'Procedimientos mínimamente invasivos',
                                ],
                                'corporal' => [
                                    'Sueroterapia' => 'Sueroterapia',
                                ],
                                default => [],
                            };
                        })
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('value')
                                ->label('Nueva subcategoría')
                                ->required(),
                        ])
                        ->createOptionUsing(fn (array $data): string => $data['value'])
                        ->columnSpan(1),
                    Forms\Components\Textarea::make('short_description')
                        ->label('Descripción corta')
                        ->rows(3)
                        ->maxLength(500)
                        ->columnSpan(2),
                ])->columns(2),

            Forms\Components\Section::make('Contenido')
                ->schema([
                    Forms\Components\RichEditor::make('description')
                        ->label('Descripción completa')
                        ->toolbarButtons([
                            'bold', 'italic', 'underline', 'strike',
                            'h2', 'h3', 'bulletList', 'orderedList',
                            'blockquote', 'link',
                        ])
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('meta.justify_description')
                        ->label('Justificar descripción en la web')
                        ->helperText('Aplica alineación justificada al contenido de la descripción en la página pública.')
                        ->default(true),
                ]),

            Forms\Components\Section::make('Imágenes')
                ->schema([
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Imagen principal (card)')
                        ->image()
                        ->disk('public')
                        ->visibility('public')
                        ->directory('services/images')
                        ->imageEditor(),
                    Forms\Components\FileUpload::make('banner_path')
                        ->label('Banner de página')
                        ->image()
                        ->disk('public')
                        ->visibility('public')
                        ->directory('services/banners')
                        ->imageEditor(),
                ])->columns(2),

            Forms\Components\Section::make('Configuración')
                ->schema([
                    Forms\Components\TextInput::make('whatsapp_text')
                        ->label('Mensaje WhatsApp personalizado')
                        ->placeholder('Hola! Me interesa información sobre...')
                        ->maxLength(300),
                    Forms\Components\Toggle::make('is_featured')
                        ->label('Destacado en homepage'),
                    Forms\Components\Toggle::make('is_premium')
                        ->label('Marcar como Premium'),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Activo / Publicado')
                        ->default(true),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Orden de aparición')
                        ->numeric()
                        ->default(0),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->size(48),
                Tables\Columns\TextColumn::make('name')
                    ->label('Procedimiento')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge(),
                Tables\Columns\TextColumn::make('meta.subcategory')
                    ->label('Subcategoría')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Destacado')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Orden')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('service_category_id')
                    ->label('Categoría')
                    ->options(ServiceCategory::pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_active')->label('Activo'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Destacado'),
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit'   => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
