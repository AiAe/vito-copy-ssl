<?php

namespace AiAe\VitoCopySSL;

use App\Plugins\RegisterSiteFeature;
use App\Plugins\RegisterSiteFeatureAction;
use Illuminate\Support\ServiceProvider;

class VitoCopySSLPluginServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function () {
            // For each feature type, register the site feature and action
            foreach (['php', 'php-blank', 'laravel'] as $featureType) {
                RegisterSiteFeature::make($featureType, 'vito-copy-ssl')
                    ->label('Copy SSL')
                    ->description('Copy certificates from another site.')
                    ->register();

                RegisterSiteFeatureAction::make($featureType, 'vito-copy-ssl', 'copy-ssl')
                    ->label('Copy')
                    ->handler(Copy::class)
                    ->register();
            }
        });
    }
}
