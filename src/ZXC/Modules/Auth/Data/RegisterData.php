<?php

namespace ZXC\Modules\Auth\Data;

use ZXC\Modules\Auth\Exceptions\InvalidEmail;
use ZXC\Modules\Auth\Exceptions\InvalidLogin;
use ZXC\Modules\Auth\Exceptions\InvalidPassword;
use ZXC\Modules\Auth\Exceptions\PasswordMismatch;

class RegisterData implements AuthenticableData
{
    /**
     * @var string
     */
    protected $login = '';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var string
     */
    protected $password = '';

    /**
     * @var string
     */
    protected $passwordFirst = '';

    /**
     * @var string
     */
    protected $passwordSecond = '';

    /**
     * @var string
     */
    protected $confirmEmailCode = '';

    /**
     * @param string $login
     * @param string $email
     * @param string $passwordFirst
     * @param string $passwordSecond
     * @throws InvalidEmail
     * @throws InvalidLogin
     * @throws InvalidPassword
     * @throws PasswordMismatch
     */
    public function __construct(
        string $login,
        string $email,
        string $passwordFirst,
        string $passwordSecond
    )
    {
        $this->login = $login;
        $this->email = $email;
        $this->passwordFirst = $passwordFirst;
        $this->passwordSecond = $passwordSecond;
        $this->validate();
        $this->password = password_hash($this->passwordFirst, PASSWORD_BCRYPT, ['cost' => 10]);
        $this->confirmEmailCode = md5(uniqid(rand(), true));
    }

    /**
     * @return bool
     * @throws InvalidEmail
     * @throws InvalidLogin
     * @throws InvalidPassword
     * @throws PasswordMismatch
     */
    public function validate(): bool
    {
        if ($this->passwordFirst !== $this->passwordSecond) {
            throw new PasswordMismatch();
        }

        if (!preg_match('/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/', $this->passwordFirst)) {
            throw new InvalidPassword();
        }

        if (filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmail();
        }

        if (!!preg_match('/^[a-z0-9]{4,30}$/', $this->login)) {
            throw new InvalidLogin();
        }
        return true;
    }

    public function getData(): array
    {
        return [
            'login' => $this->login,
            'email' => $this->email,
            'password' => $this->password,
            'confirm_email_code' => $this->confirmEmailCode,
        ];
    }
}
