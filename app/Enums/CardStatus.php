<?php
namespace App\Enums;

enum CardStatus: string
{
    case ACTIVE = 'active';
    case BLOCKED = 'blocked';
    case SUSPENDED = 'suspended';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
}
