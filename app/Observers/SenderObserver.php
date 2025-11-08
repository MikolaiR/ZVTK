<?php

namespace App\Observers;

use App\Models\Sender;

class SenderObserver
{

    protected function setName(Sender $sender): void
    {
        $sender->name = $sender->company->name;
        $sender->save();
    }
    /**
     * Handle the Sender "created" event.
     */
    public function created(Sender $sender): void
    {
        $this->setName($sender);
    }

    /**
     * Handle the Sender "updated" event.
     */
    public function updated(Sender $sender): void
    {
        $this->setName($sender);
    }

    /**
     * Handle the Sender "deleted" event.
     */
    public function deleted(Sender $sender): void
    {
        //
    }

    /**
     * Handle the Sender "restored" event.
     */
    public function restored(Sender $sender): void
    {
        //
    }

    /**
     * Handle the Sender "force deleted" event.
     */
    public function forceDeleted(Sender $sender): void
    {
        //
    }
}
