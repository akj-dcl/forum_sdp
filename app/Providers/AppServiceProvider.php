<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Google\Client as GoogleClient;
use Google\Service\Drive as GoogleDriveService;
use Masbug\Flysystem\GoogleDriveAdapter;
use League\Flysystem\Filesystem; // INI YANG TADI TYPO, SEKARANG SUDAH BENAR
use Illuminate\Filesystem\FilesystemAdapter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Sihir Penghubung PUSDAPAS ➔ Google Drive
        Storage::extend('google', function ($app, $config) {
            $client = new GoogleClient();
            $client->setClientId($config['clientId']);
            $client->setClientSecret($config['clientSecret']);
            $client->refreshToken($config['refreshToken']);

            $service = new GoogleDriveService($client);
            $adapter = new GoogleDriveAdapter($service, $config['folderId'] ?? '/', [
                'useHasDir' => true
            ]);
            
            $filesystem = new Filesystem($adapter);
            return new FilesystemAdapter($filesystem, $adapter);
        });
    }
}