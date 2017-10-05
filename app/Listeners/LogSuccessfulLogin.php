<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogSuccessfulLogin
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        session(['last_login'=>$event->user->last_login]);

        $event->user->last_login = date('Y-m-d H:i:s');
        $event->user->login_ip = $_SERVER["REMOTE_ADDR"];
        $event->user->save();
    }
}