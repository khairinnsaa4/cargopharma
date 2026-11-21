<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // On Windows or environments where storage/ is non-writable, redirect to user temp directory
        $viewsDir = storage_path('framework/views');
        if (! is_writable($viewsDir)) {
            $tempViewsDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cargopharma_views';
            if (! is_dir($tempViewsDir)) {
                @mkdir($tempViewsDir, 0777, true);
            }
            config(['view.compiled' => $tempViewsDir]);
        }

        $logsDir = storage_path('logs');
        if (! is_writable($logsDir)) {
            $tempLogsDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'cargopharma_logs';
            if (! is_dir($tempLogsDir)) {
                @mkdir($tempLogsDir, 0777, true);
            }
            config([
                'logging.channels.single.path' => $tempLogsDir.DIRECTORY_SEPARATOR.'laravel.log',
                'logging.channels.daily.path' => $tempLogsDir.DIRECTORY_SEPARATOR.'laravel.log',
                'logging.channels.stack.ignore_exceptions' => true,
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || request()->header('x-forwarded-proto') === 'https' || str_contains(request()->getHost(), 'vercel.app')) {
            URL::forceScheme('https');
        }
    }
}
