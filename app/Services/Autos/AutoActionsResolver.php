<?php

namespace App\Services\Autos;

use App\Enums\Statuses;
use App\Models\Auto;
use App\Models\User;

class AutoActionsResolver
{
    public const ACTION_MOVE_TO_CUSTOMS = 'move_to_customs';
    public const ACTION_MOVE_TO_PARKING = 'move_to_parking';
    public const ACTION_ACCEPT_AT_PARKING = 'accept_at_parking';
    public const ACTION_MOVE_TO_CUSTOMS_FROM_PARKING = 'move_to_customs_from_parking';
    public const ACTION_MOVE_TO_OTHER_PARKING = 'move_to_other_parking';
    public const ACTION_SELL = 'sell';
    public const ACTION_SAVE_FILES = 'save_files';

    public const UI_STORAGE_COST = 'storage_cost';

    /**
     * @return array<int, array{key:string,label:string,variant?:string|null}>
     */
    public function resolve(User $user, Auto $auto): array
    {
        $status = $auto->status;

        $actions = [];

        if ($this->canPerformTransition($user, $auto, self::ACTION_MOVE_TO_CUSTOMS)) {
            $actions[] = ['key' => self::ACTION_MOVE_TO_CUSTOMS, 'label' => 'Переместить на таможню'];
        }

        if ($this->canPerformTransition($user, $auto, self::ACTION_MOVE_TO_PARKING)) {
            $actions[] = ['key' => self::ACTION_MOVE_TO_PARKING, 'label' => 'Переместить на стоянку'];
        }

        if ($this->canPerformTransition($user, $auto, self::ACTION_ACCEPT_AT_PARKING)) {
            $actions[] = ['key' => self::ACTION_ACCEPT_AT_PARKING, 'label' => 'Принять на стоянку'];
        }

        if ($this->canPerformTransition($user, $auto, self::ACTION_SELL)) {
            $variant = $status === Statuses::Customer ? 'danger' : null;
            $actions[] = ['key' => self::ACTION_SELL, 'label' => 'Передана владельцу', 'variant' => $variant];
        }

        if ($this->canPerformTransition($user, $auto, self::ACTION_MOVE_TO_CUSTOMS_FROM_PARKING)) {
            $actions[] = ['key' => self::ACTION_MOVE_TO_CUSTOMS_FROM_PARKING, 'label' => 'Переместить на таможню', 'variant' => 'danger'];
        }

        if ($this->canPerformTransition($user, $auto, self::ACTION_MOVE_TO_OTHER_PARKING)) {
            $actions[] = ['key' => self::ACTION_MOVE_TO_OTHER_PARKING, 'label' => 'Переместить на другую стоянку'];
        }

        if ($this->canViewStorageCost($user, $auto)) {
            $actions[] = ['key' => self::UI_STORAGE_COST, 'label' => $status === Statuses::Sale ? 'Стоимость хранения' : 'Рассчитать стоимость хранения', 'variant' => 'outline'];
        }

        if ($this->canPerformTransition($user, $auto, self::ACTION_SAVE_FILES)) {
            $actions[] = ['key' => self::ACTION_SAVE_FILES, 'label' => 'Добавить файлы', 'variant' => 'outline'];
        }

        return $actions;
    }

    public function canPerformTransition(User $user, Auto $auto, string $action): bool
    {
        if (! $user->can('update', $auto)) {
            return false;
        }

        $status = $auto->status;

        return match ($action) {
            self::ACTION_MOVE_TO_CUSTOMS => $status === Statuses::Delivery,
            self::ACTION_MOVE_TO_PARKING => $status === Statuses::Customer,
            self::ACTION_ACCEPT_AT_PARKING => $status === Statuses::DeliveryToParking,
            self::ACTION_MOVE_TO_CUSTOMS_FROM_PARKING => $status === Statuses::Parking,
            self::ACTION_MOVE_TO_OTHER_PARKING => $status === Statuses::Parking,
            self::ACTION_SELL => ($status === Statuses::Customer || $status === Statuses::Parking),
            self::ACTION_SAVE_FILES => true,
            default => false,
        };
    }

    public function canViewStorageCost(User $user, Auto $auto): bool
    {
        return $user->can('update', $auto)
            && ($auto->status === Statuses::Parking || $auto->status === Statuses::Sale)
            && true;
    }
}
