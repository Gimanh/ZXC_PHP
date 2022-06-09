<?php

namespace ZXC\Modules\Auth;

interface AuthRemindPasswordUrlGenerator
{
    public function generate(string $code, string $login): string;
}
