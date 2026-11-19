<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportMessage extends Model
{
    protected $fillable = ['support_ticket_id', 'sender_type', 'sender_id', 'message'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function senderLabel(): string
    {
        return match ($this->sender_type) {
            'restaurant_user' => 'You',
            'admin' => 'Support team',
            'user' => 'Customer',
            default => ucfirst(str_replace('_', ' ', $this->sender_type)),
        };
    }

    public function isFromRestaurant(): bool
    {
        return $this->sender_type === 'restaurant_user';
    }
}
