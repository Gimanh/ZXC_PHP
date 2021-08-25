<?php

namespace ZXC\Modules\Auth;

use ZXC\Modules\Auth\Data\RegisterData;

interface AuthStorage
{
    public function fetchUser($login);

    public function insetUser(RegisterData $registerData):bool;
}
