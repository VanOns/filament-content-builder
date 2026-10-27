<?php

namespace VanOns\FilamentContentBuilder\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateBlockPreview
{
    public function handle(Request $request, Closure $next): Response
    {
        foreach (Filament::getPanels() as $panel) {
            $user = auth()->guard($panel->getAuthGuard())->user();

            if (!$user) {
                continue;
            }

            // Same rule as Filament's `Authenticate` middleware.
            if ($user instanceof FilamentUser ? $user->canAccessPanel($panel) : app()->isLocal()) {
                return $next($request);
            }
        }

        abort(403);
    }
}
