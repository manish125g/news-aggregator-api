<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\NewsAggregatorService;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    app(NewsAggregatorService::class)->fetchAndStoreArticles();
})->daily()->onSuccess(function () {
    Log::info('NewsAggregatorService executed successfully.');
})->onFailure(function () {
    Log::error('NewsAggregatorService execution failed.');
});
