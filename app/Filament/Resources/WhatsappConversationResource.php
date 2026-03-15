<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsappConversationResource\Pages;
use App\Models\WhatsappConversation;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WhatsappConversationResource extends Resource
{
    protected static ?string $model = WhatsappConversation::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Conversaciones WhatsApp';
    protected static ?string $modelLabel = 'Conversación';
    protected static ?string $pluralModelLabel = 'Conversaciones WhatsApp';
    protected static ?string $navigationGroup = 'WhatsApp';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.first_name')
                    ->label('Paciente')
                    ->formatStateUsing(fn ($state, $record) => $record->patient?->first_name . ' ' . $record->patient?->last_name)
                    ->searchable(query: fn ($query, $search) => $query->whereHas('patient', fn ($q) => $q->where('first_name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))),
                Tables\Columns\TextColumn::make('external_ticket_id')
                    ->label('Ticket Whaticket')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'open'   => 'success',
                        'closed' => 'gray',
                        default  => 'warning',
                    }),
                Tables\Columns\TextColumn::make('messages_count')
                    ->label('Mensajes')
                    ->counts('messages')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_message_at')
                    ->label('Último mensaje')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options(['open' => 'Abierto', 'closed' => 'Cerrado', 'pending' => 'Pendiente']),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhatsappConversations::route('/'),
        ];
    }
}
