<?php

namespace App\Observers;

use App\Models\Provider;

class ProviderObserver
{

    protected function setName(Provider $provider): void
    {
        $provider->name = $provider->company->name;
    }

    /**
     * Handle the Provider "created" event.
     */
    public function created(Provider $provider): void
    {
        $this->setName($provider);
    }

    /**
     * Handle the Provider "updated" event.
     */
    public function updated(Provider $provider): void
    {
        $this->setName($provider);
    }

    /**
     * Handle the Provider "deleted" event.
     */
    public function deleted(Provider $provider): void
    {
        //
    }

    /**
     * Handle the Provider "restored" event.
     */
    public function restored(Provider $provider): void
    {
        //
    }

    /**
     * Handle the Provider "force deleted" event.
     */
    public function forceDeleted(Provider $provider): void
    {
        //
    }
}
