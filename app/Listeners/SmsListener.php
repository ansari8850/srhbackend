<?php

namespace App\Listeners;

use App\Events\SmsEvent;
use App\Service\SMS\SMSService;
use Log;

class SmsListener
{
    /**
     * Handle the event.
     *
     * @param  \Illuminate\Auth\Events\Registered  $event
     * @return void
     */
    public function handle(SmsEvent $event)
    {

        Log::info("Inside the wattsappevent");
        Log::info(json_encode($event->smsParentObject));
        Log::info("Inside the wattsappevent1");
        $service=new SMSService;
        $service->triggerSMS($event->smsParentObject);
        
    }
}
