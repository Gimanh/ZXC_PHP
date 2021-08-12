<?php


namespace ZXC\Modules\Auth;


use ZXC\Modules\Auth\Data\LoginData;


interface Authenticable
{
    public function login(LoginData $loginData);

    public function register();

    public function confirmEmail();

    public function remindPassword();
}
