<?php

namespace ZXC\Modules\Auth\DataGenerators;

interface AuthConfirmEmailBodyGenerator
{
    public function generate(AuthConfirmEmailUrlGenerator $authConfirmEmailUrlGenerator): string;
}
