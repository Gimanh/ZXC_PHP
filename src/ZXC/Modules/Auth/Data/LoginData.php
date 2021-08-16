<?php

namespace ZXC\Modules\Auth\Data;

use ZXC\Modules\Auth\Exceptions\InvalidLogin;

class LoginData implements AuthenticableData
{
    /**
     * User login or email
     * @var string
     */
    protected string $loginOrEmail = '';

    /**
     * User password
     * @var string
     */
    protected string $password = '';

    protected bool $rememberMe = false;

    /**
     * @param string $loginOrEmail
     * @param string $password
     * @param bool $rememberMe
     * @throws InvalidLogin
     */
    public function __construct(string $loginOrEmail, string $password, bool $rememberMe = false)
    {
        $this->loginOrEmail = strtolower($loginOrEmail);
        $this->password = $password;
        $this->rememberMe = $rememberMe;
        $this->validate();
    }

    /**
     * @return bool
     * @throws InvalidLogin
     */
    public function validate(): bool
    {
        if (filter_var($this->loginOrEmail, FILTER_VALIDATE_EMAIL)) {
            $validLogin = true;
        } else {
            $validLogin = !!preg_match('/^[a-z0-9]{4,30}$/', $this->loginOrEmail);
        }

        if (!$validLogin) {
            throw new InvalidLogin();
        }

        $this->password = password_hash($this->password, PASSWORD_BCRYPT, ['cost' => 10]);

        return true;
    }

    public function getData(): array
    {
        return [
            'login' => $this->loginOrEmail,
            'password' => $this->password,
        ];
    }

}
