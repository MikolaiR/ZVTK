<?php

namespace App\Enums;

enum Statuses: int
{
    case Delivery = 1;
    case Customer = 2;
    case DeliveryToParking = 3;
    case Parking = 4;
    case Sale = 5;

    public function lable(): string
    {
        return match ($this) {
            self::Delivery => 'В доставке',
            self::Customer => 'На таможне',
            self::DeliveryToParking => 'Перемещается на стоянку',
            self::Parking => 'На стоянке',
            self::Sale => 'Продана',
        };
    }

    public function backgroundImg(): string
    {
        return match ($this) {
            self::Delivery => '/images/not_photo.png',
            self::Customer => '/images/not_photo.png',
            self::DeliveryToParking => '/images/not_photo.png',
            self::Parking => '/images/not_photo.png',
            self::Sale => '/images/not_photo.png',
        };
    }

    public function connectionWithModel(): string
    {
        return match ($this) {
            self::Delivery => 'App\Models\Sender',
            self::Customer => 'App\Models\Customer',
            self::DeliveryToParking => 'App\Models\Provider',
            self::Parking => 'App\Models\Parking',
            self::Sale => 'App\Models\Sale',
        };
    }
}
