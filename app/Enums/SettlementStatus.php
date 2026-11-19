<?php

namespace App\Enums;

enum SettlementStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Paid = 'paid';
    case Failed = 'failed';
    case OnHold = 'on_hold';
}
