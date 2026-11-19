<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Preparing = 'preparing';
    case ReadyForPickup = 'ready_for_pickup';
    case PickedUp = 'picked_up';
    case OnTheWay = 'on_the_way';
    case Delivered = 'delivered';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::ReadyForPickup => 'Ready for Pickup',
            self::OnTheWay => 'On the Way',
            default => ucfirst(str_replace('_', ' ', $this->value)),
        };
    }

    /** @return list<self> */
    public static function restaurantOngoing(): array
    {
        return [
            self::Accepted,
            self::Preparing,
            self::ReadyForPickup,
            self::PickedUp,
            self::OnTheWay,
        ];
    }

    /** @return list<string> */
    public static function restaurantOngoingValues(): array
    {
        return array_map(fn (self $status) => $status->value, self::restaurantOngoing());
    }

    /** @return list<self> */
    public static function restaurantTerminalNegative(): array
    {
        return [self::Cancelled, self::Rejected, self::Failed];
    }

    /** @return list<string> */
    public static function restaurantTerminalNegativeValues(): array
    {
        return array_map(fn (self $status) => $status->value, self::restaurantTerminalNegative());
    }
}
