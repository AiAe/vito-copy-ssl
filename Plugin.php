<?php

namespace App\Vito\Plugins\AiAe\VitoCopySsl;

use App\Plugins\AbstractPlugin;
use App\Plugins\RegisterSiteFeature;
use App\Plugins\RegisterSiteFeatureAction;
use App\Vito\Plugins\AiAe\VitoCopySsl\Actions\Copy;

class Plugin extends AbstractPlugin
{
    protected string $name = 'Vito Copy SSL';

    protected string $description = 'Copy SSL from another project';

    public function register(): void {}

    public function boot(): void
    {
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
    }
}
