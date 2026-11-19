<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('plugplay:seed-if-empty', function () {
    if (\App\Models\Product::count() === 0) {
        $this->call('db:seed', ['--force' => true]);
    } else {
        $this->info('Databáze už obsahuje data, seed přeskočen.');
    }
})->purpose('Naplní prázdnou databázi ukázkovými daty (první spuštění v Dockeru).');
