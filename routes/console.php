<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('app:health', function () {
    $this->info('Agent Business Support application is healthy.');
})->purpose('Check application health');