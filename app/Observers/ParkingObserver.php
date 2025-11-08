<?php

namespace App\Observers;

use App\Models\Parking;

class ParkingObserver
{

    protected function setName(Parking $parking): void
    {
        $parking->name = $parking->company->name . ' ' . $parking->address;
        $parking->save();
    }

    /**
     * Handle the Parking "created" event.
     */
    public function created(Parking $parking): void
    {
        $this->setName($parking);
    }

    /**
     * Handle the Parking "updated" event.
     */
    public function updated(Parking $parking): void
    {
        $this->setName($parking);
    }

    /**
     * Handle the Parking "deleted" event.
     */
    public function deleted(Parking $parking): void
    {
        //
    }

    /**
     * Handle the Parking "restored" event.
     */
    public function restored(Parking $parking): void
    {
        //
    }

    /**
     * Handle the Parking "force deleted" event.
     */
    public function forceDeleted(Parking $parking): void
    {
        //
    }

}
