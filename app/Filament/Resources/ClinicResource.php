<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClinicResource\Pages;
use App\Models\Clinic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ClinicResource extends Resource
{
    protected static ?string $model = Clinic::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Clínicas';
    protected static ?int $navigationSort = 0;

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->is_super_admin;
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->is_super_admin;
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->is_super_admin;
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->is_super_admin;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(180),
                Forms\Components\FileUpload::make('logo_path')
                    ->label('Logo de la clínica')
                    ->disk('public')
                    ->visibility('public')
                    ->directory('branding/logos')
                    ->acceptedFileTypes([
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'image/svg+xml',
                    ])
                    ->maxSize(5120)
                    ->previewable(false)
                    ->downloadable()
                    ->openable()
                    ->helperText('Formatos recomendados: SVG, PNG o WebP. El editor se desactiva para permitir logos vectoriales y transparentes.'),
                Forms\Components\TextInput::make('slug')->required()->maxLength(200)->unique(Clinic::class, 'slug', ignoreRecord: true),
                Forms\Components\TextInput::make('domain')->maxLength(255)->unique(Clinic::class, 'domain', ignoreRecord: true),
                Forms\Components\TextInput::make('timezone')->required()->default('America/Bogota')->maxLength(80),
                Forms\Components\TextInput::make('locale')->required()->default('es')->maxLength(10),
                Forms\Components\Toggle::make('is_active')->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')->label('Logo')->disk('public')->square(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\TextColumn::make('domain')->searchable()->placeholder('-'),
                Tables\Columns\TextColumn::make('timezone')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
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
            'index' => Pages\ListClinics::route('/'),
            'create' => Pages\CreateClinic::route('/create'),
            'edit' => Pages\EditClinic::route('/{record}/edit'),
        ];
    }
}
