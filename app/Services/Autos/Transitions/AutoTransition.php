<?php

namespace App\Services\Autos\Transitions;

use App\Models\Auto;
use App\Models\User;

interface AutoTransition
{
    /**
     * @param array $data Validated payload from TransitionRequest
     */
    public function handle(Auto $auto, array $data, User $actor): void;
}
