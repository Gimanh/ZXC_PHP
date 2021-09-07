<?php

namespace ZXC\Modules\Auth;

use ZXC\Interfaces\Psr\Http\Message\RequestInterface;
use ZXC\Modules\Auth\Data\LoginData;
use ZXC\Modules\Auth\Data\RegisterData;
use ZXC\Modules\Auth\Data\ConfirmEmailData;
use ZXC\Modules\Auth\Data\ChangePasswordData;
use ZXC\Modules\Auth\Data\RemindPasswordData;
use ZXC\Modules\Auth\Data\ChangeRemindedPasswordData;

interface Authenticable
{
    public function login(LoginData $data);

    public function logout();

    public function register(RegisterData $data);

    public function confirmEmail(ConfirmEmailData $data);

    public function remindPassword(RemindPasswordData $data);

    public function changeRemindedPassword(ChangeRemindedPasswordData $data);

    public function changePassword(ChangePasswordData $data);

    public function getUser(): UserModel;

    public function retrieveFromRequest(RequestInterface $request): UserModel;

}
