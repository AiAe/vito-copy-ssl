<?php

namespace AiAe\VitoCopySSL;

use App\Actions\SSL\CreateSSL;
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
        return DynamicForm::make([
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
