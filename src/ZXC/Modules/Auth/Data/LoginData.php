<?php

namespace ZXC\Modules\Auth\Data;

use Closure;
use InvalidArgumentException;


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

    /**
     * Function for custom password validation
     * @var Closure
     */
    protected Closure $passwordValidator;

    public function __construct(string $loginOrEmail, string $password, Closure $passwordValidator)
    {
        $this->loginOrEmail = strtolower($loginOrEmail);
        $this->password = $password;
        $this->passwordValidator = $passwordValidator;
        $this->validate();
    }

    public function validate(): bool
    {
        if (filter_var($this->loginOrEmail, FILTER_VALIDATE_EMAIL)) {
            $validLogin = true;
        } else {
            $validLogin = !!preg_match('/^[a-z0-9]{4,30}$/', $this->loginOrEmail);
        }

        if (!$validLogin) {
            throw new InvalidArgumentException('Can not validate login. Rule is "/^[a-z0-9]{4,30}$/".');
        }

        if ($this->passwordValidator) {
            $validatePassword = call_user_func($this->passwordValidator, $this->password);
        } else {
            $validatePassword = preg_match('/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/', $this->password);
        }

        if (!$validatePassword) {
            throw new InvalidArgumentException('Can not validate password.');
        }

        $this->password = password_hash($this->password, PASSWORD_BCRYPT, ['cost' => 10]);

        return true;
    }

    /**
     * @return string
     */
    public function getLoginOrEmail(): string
    {
        return $this->loginOrEmail;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

}
