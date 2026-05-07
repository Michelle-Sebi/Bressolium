<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyPlayersListener implements ShouldQueue
{
    public function handle(object $event): void {}
}
