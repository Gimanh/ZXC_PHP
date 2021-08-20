<?php

namespace ZXC\Modules\Auth\Data;

use ZXC\Modules\Auth\Exceptions\InvalidConfirmEmailArgs;

class ConfirmEmailData implements AuthenticableData
{

    protected $login = '';

    protected $code = '';

    /**
     * @param string $login
     * @param string $code
     * @throws InvalidConfirmEmailArgs
     */
    public function __construct(string $login, string $code)
    {
        $this->login = $login;
        $this->code = $code;
        $this->validate();
    }

    /**
     * @return bool
     * @throws InvalidConfirmEmailArgs
     */
    public function validate(): bool
    {
        if ($this->login && $this->code) {
            return true;
        }

        throw new InvalidConfirmEmailArgs();
    }

    public function getData(): array
    {
        return [
            'login' => $this->login,
            'code' => $this->code,
        ];
    }
}
