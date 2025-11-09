<?php

namespace App\Observers;

use App\Models\Auto;
use App\Models\Sender;
use App\Models\Provider;
use Illuminate\Support\Facades\Auth;

class AutoObserver
{

    protected function setTitle(Auto $auto): void
    {
        $auto->title = $auto->brand->name . ' ' . $auto->model->name . ' ' . $auto->vin;
    }

    protected function setProviderAndSender(Auto $auto): void
    {
        $user = Auth::user();
        $auto->provider_id = Provider::where('user_id', $user->id)->first()->id;
        $auto->sender_id =  Sender::where('user_id', $user->id)->first()->id;
        $auto->company_id = $user->company_id;
    }

    /**
     * Handle the Auto "created" event.
     */
    public function created(Auto $auto): void
    {
        $this->setTitle($auto);
        $this->setProviderAndSender($auto);
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
