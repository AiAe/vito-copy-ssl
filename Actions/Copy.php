<?php

namespace App\Vito\Plugins\RichardAnderson\VitoCopySsl\Actions;

use App\Actions\SSL\CreateSSL;
use App\DTOs\DynamicField;
use App\DTOs\DynamicForm;
use App\Models\Ssl;
use App\SiteFeatures\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Copy extends Action
{
    public function name(): string
    {
        return 'Copy';
    }

    public function active(): bool
    {
        return true;
    }

    public function form(): ?DynamicForm
    {
        // Fetch active custom SSLs from other sites
        $ssls = Ssl::query()
            ->select(['id', 'domains', 'is_active'])
            ->where('site_id', '!=', $this->site->id)
            ->where('type', 'custom')
            ->where('is_active', true)
            ->get()
            ->map(fn ($ssl) => head($ssl->domains))
            ->toArray();

        return DynamicForm::make([
            DynamicField::make('alert')
                ->alert()
                ->label('Important!')
                ->description('Plugin is work in progress. Use at your own risk.'),

            DynamicField::make('site')
                ->select()
                ->options($ssls)
                ->label('Sites')
                ->description('Select which site to copy the SSL certificate from.'),
        ]);
    }

    public function handle(Request $request): void
    {
        Validator::make($request->all(), [
            'site' => 'required|string',
        ])->validate();

        $site = $request->input('site');

        $ssl = Ssl::query()->whereJsonContains('domains', $site)->firstOrFail();

        app(CreateSSL::class)->create($this->site, [
            'type' => 'custom',
            'certificate' => $ssl->certificate,
            'private' => $ssl->pk,
            'expires_at' => $ssl->expires_at->format('Y-m-d'),
        ]);

        $request->session()->flash('success', 'Successfully copied SSL certificate from '.$site);
    }
}
