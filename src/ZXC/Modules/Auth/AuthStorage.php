<?php

namespace ZXC\Modules\Auth;

use ZXC\Modules\Auth\Data\RegisterData;

interface AuthStorage
{
    const USER_NOT_INSERTED = -1;

    public function fetchUser($login);

    /**
     * Add user to database
     * @param RegisterData $registerData
     * @return int -1 or inserted user id
     */
    public function insetUser(RegisterData $registerData): int;
}
