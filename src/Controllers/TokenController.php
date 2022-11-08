<?php

namespace Ikechukwukalu\Tokenmiddleware\Controllers;

use Ikechukwukalu\Tokenmiddleware\Controllers\Controller;
use Ikechukwukalu\Tokenmiddleware\Rules\CurrentToken;
use Ikechukwukalu\Tokenmiddleware\Rules\DisallowOldToken;

use Illuminate\Validation\Rules\Password;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Crypt;
use Ikechukwukalu\Tokenmiddleware\Notifications\TokenChange;

use App\Models\User;
use Ikechukwukalu\Tokenmiddleware\Models\RequireToken;
use Ikechukwukalu\Tokenmiddleware\Models\OldToken;

class TokenController extends Controller
{
    protected $maxAttempts = 3;
    protected $delayMinutes = 1;

    public function __construct()
    {
        $this->maxAttempts = config('tokenmiddleware.token.maxAttempts', 3);
        $this->delayMinutes = config('tokenmiddleware.token.delayMinutes', 1);
    }

    /**
     * User change token.
     *
     * Within the config file, you are required to determine the number
     * of previously used tokens a User is not allowed to use anymore
     * by setting <b>token.check_all</b> to <b>TRUE/FALSE</b> or to an <b>int</b>
     * value and <b>token.number</b> to a corresponding <b>int</b>
     * value as well.
     *
     * You can choose to notify a User whenever a token is changed by setting
     * <b>token.notify.change</b> to <b>TRUE</b>
     *
     * @bodyParam current_token string required The user's token. Example: @wE3456qas@$
     * @bodyParam token string required The token for user authentication must contain only numbers. Example: Ex@m122p$%l6E
     * @bodyParam token_confirmation string required Must match <small class="badge badge-blue">token</small> Field. Example: Ex@m122p$%l6E
     *
     * @response 200
     *
     * {
     * "status": "success",
     * "status_code": 200,
     * "data": {
     *      "message": string
     *  }
     * }
     *
     * @authenticated
     * @group Auth APIs
     */

    public function changeToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_token' => ['required', 'string', new CurrentToken(true)],
            'token' => [
                        'required', 'string',
                        'max:' . config('tokenmiddleware.token.max', 4),
                        Password::min(config('tokenmiddleware.token.min', 4))
                        ->numbers()->letters()->mixedCase(),
                        'confirmed',
                        new DisallowOldToken(
                            config('tokenmiddleware.token.check_all', true),
                            config('tokenmiddleware.token.number', 4)
                        )
                    ],
        ]);

        if ($validator->fails()) {
            $data = ['message' => (array) $validator->errors()->all()];
            return $this->httpJsonResponse(trans('tokenmiddleware::general.fail'), 500, $data);
        }

        $user = Auth::user();
        $user->token = Hash::make($request->token);
        $user->default_token = $request->current_token !== config('tokenmiddleware.token.default', '0000');

        if ($user->save()) {
            OldToken::create([
                'user_id' => $user->id,
                'token' => Hash::make($request->token)
            ]);
        }

        if (config('tokenmiddleware.token.notify.change', true)) {
            $user->notify(new TokenChange());
        }

        $data = ['message' => trans('tokenmiddleware::token.changed')];
        return $this->httpJsonResponse(trans('tokenmiddleware::general.success'), 200, $data);
    }

    /**
     * User token authentication.
     *
     * @bodyParam _token string required The user's token must contain only numbers. Example: 0000
     * @urlParam uuid string required Example: eab8cce0-bb22-4c53-8924-b885ebb67f5a
     *
     * @authenticated
     * @group Auth APIs
     * @subgroup Require Token APIs
     * @subgroupDescription <b>require.token</b> middleware can
     * be added to a route to require token authentication before
     * processing any request to that route. The <b>require.token</b>
     * middleware would arrest any incoming request and return a laravel
     * signed temporary URL via the route specified in <b>token.route</b>.
     * The User is meant to carryout a token authentication over the
     * returned URL and the <b>require.token</b> middleware would process
     * the previously arrested request if the authentication is successful.
     *
     * Within the config file, use the <b>token.maxAttempts</b> and
     * the <b>token.delayMinutes</b> to adjust the route throttling for
     * token authentication.
     */

    public function tokenRequired(Request $request, $uuid)
    {
        if ($this->hasTooManyAttempts($request)) {
            $this->_fireLockoutEvent($request);

            $data = ["message" => trans('tokenmiddleware::token.throttle',
                        ['seconds' => $this->_limiter()
                            ->availableIn($this->_throttleKey($request))
                        ])
                    ];
            return $this->httpJsonResponse(trans('tokenmiddleware::general.fail'), 500, $data);
        }

        $this->incrementAttempts($request);

        if (!$request->hasValidSignature()) {
            return $this->httpJsonResponse(trans('tokenmiddleware::general.fail'),
                    401, ['message' => trans('tokenmiddleware::token.expired_url')]);
        }

        $user = Auth::user();

        $requireToken = RequireToken::whereBelongsTo($user)
                        ->where('uuid', $uuid)
                        ->whereNull('approved_at')
                        ->whereNull('cancelled_at')
                        ->first();

        if (!isset($requireToken->id)) {
            return $this->httpJsonResponse(trans('tokenmiddleware::general.fail'),
                    401, ['message' => trans('tokenmiddleware::token.invalid_url')]);
        }

        $validator = Validator::make($request->all(), [
            config('tokenmiddleware.token.input', '_token') => ['required', 'string', new CurrentToken],
        ]);

        if ($validator->fails()) {
            $data = ['message' => (array) $validator->errors()->all()];
            return $this->httpJsonResponse(trans('tokenmiddleware::general.fail'), 500, $data);
        };

        if (config('tokenmiddleware.token.verify_sender', true)) {
            if (
                $requireToken->ip !== $this->getClientIp($request) ||
                $requireToken->device !== $request->userAgent()
            ) {
                return $this->httpJsonResponse(trans('tokenmiddleware::general.fail'),
                        406, ['message' => trans('tokenmiddleware::token.unverified_sender')]);
            }
        }

        $this->updateRequest($request, unserialize(Crypt::decryptString($requireToken->payload)));

        $request = Request::create($requireToken->route_arrested, $requireToken->method, ['_uuid' => $uuid]);
        $response = Route::dispatch($request);

        $this->clearAttempts($request);

        return $response;
    }

    public function tokenRequestTerminated(string $url): JsonResponse
    {
        return $this->httpJsonResponse(
            trans('tokenmiddleware::general.fail'), 401,
            [
                'message' => trans('tokenmiddleware::token.terminated'),
                'url' => $url
            ]
        );
    }

    public function tokenValidationURL(string $url, null|string $redirect): JsonResponse
    {
        return $this->httpJsonResponse(
            trans('tokenmiddleware::general.success'), 200,
            [
                'message' => trans('tokenmiddleware::token.require_token'),
                'url' => $url,
                'redirect' => $redirect
            ]
        );
    }

    private function updateRequest(Request $request, array $payload): void
    {
        $request->merge([
            'expires' => null,
            'signature' => null,
            config('tokenmiddleware.token.input', '_token') => null
        ]);

        foreach($payload as $key => $item) {
            $request->merge([$key => $payload[$key]]);
        }
    }
}
