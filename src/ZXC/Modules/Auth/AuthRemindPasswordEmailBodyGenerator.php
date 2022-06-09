<?php

namespace ZXC\Modules\Auth;

interface AuthRemindPasswordEmailBodyGenerator
{
    public function generate(string $link): string;
}
