<?php

namespace Ikechukwukalu\Tokenmiddleware\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ikechukwukalu\Tokenmiddleware\Controllers\TokenController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;

use Ikechukwukalu\Tokenmiddleware\Models\RequireToken as RequireTokenModel;

class RequireToken
{

    public function handle(Request $request, Closure $next)
    {
        $tokenController = new TokenController();

        if (!Auth::check()) {
            return $tokenController->tokenRequestTerminated();
        }

        $user = Auth::user();

        if ($request->has(config('tokenmiddleware.token.param', '_uuid'))) {
            $param = config('tokenmiddleware.token.param', '_uuid');
            $requireToken = RequireTokenModel::whereBelongsTo($user)
                            ->where('route_arrested', $request->path())
                            ->where('uuid', $request->{$param})
                            ->whereNull('approved_at')
                            ->whereNull('cancelled_at')
                            ->first();

            if (isset($requireToken->id)) {
                $requireToken->approved_at = now();
                $requireToken->save();

                return $next($request);
            }

        }

        RequireTokenModel::whereBelongsTo($user)
            ->whereNull('approved_at')
            ->whereNull('cancelled_at')
            ->update(['cancelled_at' => now()]);

        $redirect_to = config('tokenmiddleware.token.redirect_to', null);
        $uuid = (string) Str::uuid();
        $expires_at = now()->addSeconds(
            config('tokenmiddleware.token.duration', null));

        $token_validation_url = URL::temporarySignedRoute(
            config('tokenmiddleware.token.route', 'require_token'),
            $expires_at, ['uuid' => $uuid]);

        RequireTokenModel::create([
            "user_id" => $user->id,
            "uuid" => $uuid,
            "ip" => $tokenController->getClientIp($request),
            "device" => $request->userAgent(),
            "method" => $request->method(),
            "route_arrested" => $request->path(),
            "payload" => Crypt::encryptString(serialize($request->all())),
            "redirect_to" => $redirect_to,
            "token_validation_url" => $token_validation_url,
            "expires_at" => $expires_at
        ]);

        return $tokenController->tokenValidationURL($token_validation_url, $redirect_to);
    }

}
