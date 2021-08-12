<?php


namespace ZXC\Modules\Auth\Data;


class RegisterData
{
    protected string $login = '';

    protected string $email = '';

    protected string $passwordFirst = '';

    protected string $passwordSecond = '';

    public function __construct(string $login, string $email, string $passwordFirst, string $passwordSecond)
    {
        $this->login = $login;
        $this->email = $email;
        $this->passwordFirst = $passwordFirst;
        $this->passwordSecond = $passwordSecond;
    }
}
