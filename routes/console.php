<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('laralog:hello', function () {
    $this->info('Laralogger CLI is active.');
})->describe('Test command for Laralogger (you can remove this).');