<?php

namespace Ikechukwukalu\Tokenmiddleware\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CurrentToken implements Rule
{

    private bool $defaultToken = false;
    private bool $allowDefaultToken = false;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(bool $allowDefaultToken = false)
    {
        //
        $this->allowDefaultToken = $allowDefaultToken;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (Auth::user()->default_token && !$this->allowDefaultToken) {
            $this->defaultToken = true;

            return false;
        }

        return Hash::check($value, Auth::user()->token);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->defaultToken) {
            return trans('tokenmiddleware::token.default');
        }

        return trans('tokenmiddleware::token.wrong');
    }
}
