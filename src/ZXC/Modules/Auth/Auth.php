<?php

namespace ZXC\Modules\Auth;

use ZXC\Traits\Module;
use ZXC\Interfaces\IModule;
use ZXC\Modules\Auth\Data\LoginData;
use ZXC\Modules\Auth\Data\RegisterData;
use ZXC\Modules\Auth\Data\ConfirmEmailData;
use ZXC\Modules\Auth\Data\RemindPasswordData;
use ZXC\Modules\Auth\Data\ChangePasswordData;
use ZXC\Modules\Auth\Data\ChangeRemindedPasswordData;

class Auth implements Authenticable, IModule
{
    use Module;

    public function init(array $options = [])
    {
        // TODO: Implement init() method.
    }

    public function login(LoginData $data)
    {
        // TODO: Implement login() method.
    }

    public function register(RegisterData $data)
    {
        // TODO: Implement register() method.
    }

    public function confirmEmail(ConfirmEmailData $data)
    {
        // TODO: Implement confirmEmail() method.
    }

    public function remindPassword(RemindPasswordData $data)
    {
        // TODO: Implement remindPassword() method.
    }

    public function changeRemindedPassword(ChangeRemindedPasswordData $data)
    {
        // TODO: Implement changeRemindedPassword() method.
    }

    public function changePassword(ChangePasswordData $data)
    {
        // TODO: Implement changePassword() method.
    }
}
