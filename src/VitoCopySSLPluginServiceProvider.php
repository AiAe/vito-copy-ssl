<?php

namespace AiAe\VitoCopySSL;

use App\DTOs\DynamicField;
use App\DTOs\DynamicForm;
use App\Models\Ssl;
use App\Plugins\RegisterSiteFeature;
use App\Plugins\RegisterSiteFeatureAction;
use Illuminate\Support\ServiceProvider;

class VitoCopySSLPluginServiceProvider extends ServiceProvider
{
    private array $featureTypes = [
        'php',
        'php-blank',
        'laravel',
    ];

    public function register(): void {}

    public function boot(): void
    {
        $this->app->booted(function () {
            // Fetch active custom SSLs from other sites
            $ssls = Ssl::query()
                ->select(['id', 'domains', 'is_active'])
                ->where('type', 'custom')
                ->where('is_active', true)
                ->get()
                ->map(fn ($ssl) => head($ssl->domains))
                ->toArray();

            // For each feature type, register the site feature and action
            foreach ($this->featureTypes as $featureType) {
                RegisterSiteFeature::make($featureType, 'vito-copy-ssl')
                    ->label('Copy SSL')
                    ->description('Copy certificates from another site.')
                    ->register();

                RegisterSiteFeatureAction::make($featureType, 'vito-copy-ssl', 'copy-ssl')
                    ->label('Copy')
                    ->form(DynamicForm::make([
                        DynamicField::make('alert')
                            ->alert()
                            ->label('Important!')
                            ->description('Plugin is work in progress. Use at your own risk.'),

                        DynamicField::make('site')
                            ->select()
                            ->options($ssls)
                            ->label('Sites')
                            ->description('Select which site to copy the SSL certificate from.'),
                    ]))
                    ->handler(Copy::class)
                    ->register();
            }
        });
    }
}
