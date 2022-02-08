<?php

namespace ZXC\Modules\Auth;

interface AuthLoginProvider
{
    public function __construct(array $config);

    public function provide(array $userData): array;

    public function getLoginType(): string;

    public function logout(int $userId, string $token): bool;
}
