<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasTenant;

class WhatsappMessage extends Model
{
    use HasFactory;
    use HasTenant;

    protected static function booted(): void
    {
        static::creating(function (WhatsappMessage $message) {
            if (empty($message->client_id) && $message->conversation_id) {
                $conv = WhatsappConversation::find($message->conversation_id);
                if ($conv) {
                    $message->client_id = $conv->client_id ?? null;
                }
            }
        });
    }

    protected $fillable = [
        'conversation_id',
        'external_message_id',
        'direction',
        'body',
        'payload',
        'sent_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsappConversation::class, 'conversation_id');
    }
}
