<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // PAKSA SEMUA PENGATURAN KE INDONESIA
        config(['app.locale' => 'id']);
        Carbon::setLocale('id');
        
        // Tambahan: Set locale PHP native (penting untuk server Windows/Linux)
        setlocale(LC_TIME, 'id_ID.utf8', 'id_ID', 'id', 'ID', 'Indonesian_indonesia.1252', 'Windows-1252');
        date_default_timezone_set('Asia/Jakarta');
    }
}