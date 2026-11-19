<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanelNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'notifiable_type', 'notifiable_id', 'title', 'body', 'type', 'is_read', 'data',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'data' => 'array',
        ];
    }

    public function scopeForAdmin($query)
    {
        return $query->where('notifiable_type', 'admin');
    }

    public function scopeForRestaurant($query, int $restaurantId)
    {
        return $query->where('notifiable_type', 'restaurant')
            ->where('notifiable_id', $restaurantId);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function typeLabel(): string
    {
        return app(\App\Services\RestaurantNotificationService::class)->typeMeta($this->type)['label'];
    }

    public function actionUrl(): ?string
    {
        $url = $this->data['url'] ?? null;

        return is_string($url) && $url !== '' ? $url : null;
    }
}
