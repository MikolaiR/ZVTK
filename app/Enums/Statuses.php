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
            self::Delivery => '/images/delivery.webp',
            self::Customer => '/images/customer.webp',
            self::DeliveryToParking => '/images/delivery_to_parking.webp',
            self::Parking => '/images/parking.webp',
            self::Sale => '/images/sale.webp',
        };
    }

    public function connectionWithModel(): string
    {
        return match ($this) {
            self::Delivery => 'App\Models\Sender',
            self::Customer => 'App\Models\Customer',
            self::DeliveryToParking => 'App\Models\Parking',
            self::Parking => 'App\Models\Parking',
            self::Sale => 'App\Models\AutoSale',
        };
    }

    /**
     * Get the permission name for viewing autos with this status.
     */
    public function permissionName(): string
    {
        return 'view_status_' . strtolower($this->name);
    }

    /**
     * Get all permission names for all statuses.
     *
     * @return array<string>
     */
    public static function allPermissionNames(): array
    {
        return array_map(
            fn(self $status) => $status->permissionName(),
            self::cases()
        );
    }

    /**
     * Get statuses that the user is allowed to view based on their permissions.
     *
     * @param \App\Models\User|null $user
     * @return array<self>
     */
    public static function allowedFor(?\App\Models\User $user): array
    {
        if ($user === null) {
            return [];
        }

        return array_filter(
            self::cases(),
            fn(self $status) => $user->can($status->permissionName())
        );
    }

    /**
     * Get status values that the user is allowed to view.
     *
     * @param \App\Models\User|null $user
     * @return array<int>
     */
    public static function allowedValuesFor(?\App\Models\User $user): array
    {
        return array_map(
            fn(self $status) => $status->value,
            self::allowedFor($user)
        );
    }
}
