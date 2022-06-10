<?php

namespace ZXC\Modules\Auth\DataGenerators;

interface AuthConfirmEmailUrlGenerator
{
    public function generate(string $code, string $login): string;
}
