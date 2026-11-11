<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema; // 1. Tambahkan baris ini

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191); // 2. Tambahkan baris ini
    }
}