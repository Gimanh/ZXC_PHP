<?php

namespace ZXC\Modules\Auth;

interface AuthConfirmEmailUrlGenerator
{
    public function generate(string $code, string $login): string;
}
