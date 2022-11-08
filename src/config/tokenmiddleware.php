<?php

return [
    /**
     * Token configurations
     */
    'token' => [
        /**
         * int - Default token
         */
        'default' => '0000',
        /**
         * int - Uses seconds. Make sure to update the 'expires_at'
         * column if you changed this value after migration
         */
        'duration' => 300,
        /**
         * string|null - Make sure to update the 'redirect_to'
         * column if you changed this value after migration
         */
        'redirect_to' => null,
        /**
         * boolean
         */
        'verify_sender' => true,
        /**
         * string - Name of form input
         */
        'input' => '_token',
        /**
         * string - Name of URL param
         */
        'param' => '_uuid',
        /**
         * string - Name of route
         */
        'route' => 'require_token',
        /**
         * array|string|null - Locks a route group
         * null - indicates that the middleware is
         * not acting as OTP for a route group rather for
         * a just a single route.
         * You can also choose to add the routes as middleware
         * parameters.
         */
        '2fa_routes' => null,
        /**
         * bool - Locks a route group
         */
        'exempt' => [],
        /**
         * int - Max chars for token
         */
        'max' => 4,
        /**
         * int - Min chars for token
         */
        'min' => 4,
        /**
         * int|boolean - Check all or a specified number of
         * previous passwords
         */
        'check_all' => true,
        /**
         * int - Number of previous tokens to check
         */
        'number' => 4,
        /**
         * int - Number of times a user is allowed to authenticate
         * using his token
         */
        'maxAttempts' => 3,
        /**
         * int - Number of times a user is allowed to authenticate
         * using his token
         */
        'delayMinutes' => 1,

        /**
         * Token notification configurations
         */
        'notify' => [
            /**
             * boolean - Send a notification whenever token is changed
             */
            'change' => true,
        ]
    ]
];
