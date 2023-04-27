<?php

namespace Aoeng\Laravel\Huifu;

use Illuminate\Support\ServiceProvider;

class HuifuServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/huifu.php' => config_path('huifu.php'),
        ], 'huifu');

    }

    public function register()
    {
        $this->app->bind('huifu', function ($app) {
            $config = $app->make('config')->get('huifu', []);

            return new HuiFu($config);
        });
    }

}