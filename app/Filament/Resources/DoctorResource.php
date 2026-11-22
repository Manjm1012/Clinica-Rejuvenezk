<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\AdminOnly;
use App\Filament\Resources\DoctorResource\Pages;
use App\Models\Doctor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DoctorResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = Doctor::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Contenido del Sitio';

    protected static ?string $navigationLabel = 'Médicos / Especialistas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(200),
                Forms\Components\TextInput::make('specialty')->maxLength(200),
                Forms\Components\TextInput::make('subtitle')->maxLength(300),
                Forms\Components\FileUpload::make('photo_path')
                    ->label('Foto del médico')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('doctors')
                    ->imageEditor(),
                Forms\Components\Textarea::make('bio')->columnSpanFull(),
                Forms\Components\TextInput::make('university')->maxLength(200),
                Forms\Components\TextInput::make('certifications')->maxLength(255),
                Forms\Components\TextInput::make('linkedin_url')->url()->maxLength(255),
                Forms\Components\TextInput::make('instagram_url')->url()->maxLength(255),
                Forms\Components\Section::make('Datos para la fórmula médica')
                    ->description('Aparecen en las fórmulas que emite este médico.')
                    ->schema([
                        Forms\Components\TextInput::make('professional_registration')
                            ->label('Registro profesional (RETHUS / tarjeta profesional)')
                            ->maxLength(60),
                        // Disco privado: la firma nunca tiene URL pública; se incrusta al imprimir.
                        Forms\Components\FileUpload::make('signature_path')
                            ->label('Firma escaneada (PNG con fondo transparente)')
                            ->image()
                            ->disk('local')
                            ->directory('signatures')
                            ->visibility('private')
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                            ->maxSize(1024),
                    ])->columns(2)->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')->default(true),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')->label('')->disk('public')->circular(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('specialty')->wrap(),
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
            'index' => Pages\ListDoctors::route('/'),
            'create' => Pages\CreateDoctor::route('/create'),
            'edit' => Pages\EditDoctor::route('/{record}/edit'),
        ];
    }
}
