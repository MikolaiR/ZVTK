<?php

namespace App\Observers;

use App\Models\Auto;

class AutoObserver
{

    protected function setTitle(Auto $auto): void
    {
        $auto->title = $auto->brand->name . ' ' . $auto->model->name . ' ' . $auto->vin;
        $auto->save();
    }

    /**
     * Handle the Auto "created" event.
     */
    public function created(Auto $auto): void
    {
        $this->setTitle($auto);
    }

    /**
     * Handle the Auto "updated" event.
     */
    public function updated(Auto $auto): void
    {
        $this->setTitle($auto);
    }

    /**
     * Handle the Auto "deleted" event.
     */
    public function deleted(Auto $auto): void
    {
        //
    }

    /**
     * Handle the Auto "restored" event.
     */
    public function restored(Auto $auto): void
    {
        //
    }

    /**
     * Handle the Auto "force deleted" event.
     */
    public function forceDeleted(Auto $auto): void
    {
        //
    }
}
