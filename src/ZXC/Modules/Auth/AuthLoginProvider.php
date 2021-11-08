<?php

namespace ZXC\Modules\Auth;

use ZXC\Interfaces\Psr\Http\Message\ResponseInterface;

interface AuthLoginProvider
{
    public function __construct(array $config);

    public function provide(array $userData, ResponseInterface $response): ResponseInterface;

    public function getLoginType(): string;
}
