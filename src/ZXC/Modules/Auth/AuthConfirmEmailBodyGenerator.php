<?php

namespace ZXC\Modules\Auth;

interface AuthConfirmEmailBodyGenerator
{
    public function generate(string $link): string;
}
