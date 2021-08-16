<?php


namespace ZXC\Modules\Auth;


use ZXC\Modules\Auth\Data\ConfirmEmailData;
use ZXC\Modules\Auth\Data\LoginData;
use ZXC\Modules\Auth\Data\RegisterData;
use ZXC\Modules\Auth\Data\RemindPasswordData;


interface Authenticable
{
    public function login(LoginData $data);

    public function register(RegisterData $data);

    public function confirmEmail(ConfirmEmailData $data);

    public function remindPassword(RemindPasswordData $data);
}
