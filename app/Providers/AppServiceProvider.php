<?php

namespace App\Providers;

use App\Interfaces\HtmlParserInterface;
use App\Services\Parsers\SymfonyHtmlParser;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
