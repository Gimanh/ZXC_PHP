<?php

namespace ZXC\Modules\Auth\Data;

use ZXC\Modules\Auth\Exceptions\InvalidRemindPasswordArgs;

class RemindPasswordData implements AuthenticableData
{

    protected $code = '';

    protected $login = '';

    /**
     * @param string $login
     * @throws InvalidRemindPasswordArgs
     */
    public function __construct(string $login)
    {
        $this->login = $login;
        $this->code = md5(uniqid(rand(), true));
        $this->validate();
    }

    /**
     * @return bool
     * @throws InvalidRemindPasswordArgs
     */
    public function validate(): bool
    {
        if ($this->login) {
            return true;
        }
        throw new InvalidRemindPasswordArgs();
    }

    public function getData(): array
    {
        return [
            'login' => $this->login,
            'code' => $this->code,
        ];
    }
}
