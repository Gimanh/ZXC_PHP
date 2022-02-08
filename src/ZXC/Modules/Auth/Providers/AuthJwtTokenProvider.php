<?php

namespace ZXC\Modules\Auth\Providers;

use ZXC\Modules\Auth\Auth;
use ZXC\Native\FromGlobals;
use ZXC\Native\JWT\JWTToken;
use InvalidArgumentException;
use ZXC\Modules\Auth\AuthTokenStorage;
use ZXC\Modules\Auth\AuthLoginProvider;
use ZXC\Interfaces\Psr\Http\Message\ResponseInterface;

class AuthJwtTokenProvider implements AuthLoginProvider
{
    protected array $config = [];

    protected ?AuthTokenStorage $tokenStorage = null;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->checkConfig();
        $this->tokenStorage = new $this->config['tokenStorage']();
    }

    protected function checkConfig()
    {
        $required = ['secret', 'alg', 'accessLifeTime', 'refreshLifetime', 'tokenStorage'];
        foreach ($required as $key) {
            if (!isset($this->config[$key])) {
                throw new InvalidArgumentException('Config "' . $key . '" is required for provider "' . $this->getLoginType() . '"');
            }
        }
    }

    public function login(array $userData): array
    {
        $rowId = $this->tokenStorage->initTokenRecord($userData['id'], FromGlobals::getIp());
        $tokens = $this->generateTokens($rowId, $userData);
        $updateResult = $this->tokenStorage->updateTokens($tokens['access'], $tokens['refresh'], $rowId);
        if ($updateResult) {
            return $this->addTokensToResponse($tokens['access'], $tokens['refresh'], $userData);
        }
        return [];
    }

    protected function generateTokens(int $rowId, array $userData): array
    {
        $accessToken = JWTToken::encode([
            'id' => $rowId,
            'userData' => $userData,
            'type' => $this->getLoginType(),
            'exp' => $this->getAccessExpireTime(),
        ], $this->config['secret'], $this->config['alg']);

        $refreshToken = JWTToken::encode([
            'id' => $rowId,
            'userData' => $userData,
            'type' => $this->getLoginType(),
            'exp' => $this->getRefreshExpireTime(),
        ], $this->config['secret'], $this->config['alg']);

        return ['access' => $accessToken, 'refresh' => $refreshToken];
    }

    protected function addTokensToResponse($access, $refresh, $userData): array
    {
        return [
            'access' => $access,
            'refresh' => $refresh,
            'type' => $this->getLoginType(),
            'userData' => $userData,
        ];
    }

    protected function getAccessExpireTime(): int
    {
        return time() + ($this->config['accessLifeTime'] * 60);
    }

    protected function getRefreshExpireTime(): int
    {
        return time() + ($this->config['refreshLifetime'] * 60);
    }

    protected function getSecretKey()
    {
        return $this->config['secret'];
    }

    public function getLoginType(): string
    {
        return Auth::AUTH_TYPE_JWT;
    }

    public function updateTokensByRefreshToken(ResponseInterface $response, string $refreshToken = '')
    {
        $decoded = JWTToken::decode($refreshToken, $this->getSecretKey());
        $rowId = $decoded['id'];
        $tokens = $this->tokenStorage->fetchTokens($decoded['id']);
        if ($tokens && isset($tokens['refresh_token']) && $refreshToken === $tokens['refresh_token']) {
            $newTokens = $this->generateTokens($rowId, $decoded['userData']);
            $updateResult = $this->tokenStorage->updateTokens($newTokens['access'], $newTokens['refresh'], $rowId);
            if ($updateResult) {
                return $this->addTokensToResponse($response, $newTokens['access'], $newTokens['refresh'], $decoded['userData']);
            }
        }
        return $response->withStatus(400);
    }

    public function decodeToken($token)
    {
        return JWTToken::decode($token, $this->getSecretKey());
    }

    /**
     * @return AuthTokenStorage|null
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    public function logout(int $userId, string $token): bool
    {
        return $this->tokenStorage->deleteTokens($userId, $token);
    }
}
