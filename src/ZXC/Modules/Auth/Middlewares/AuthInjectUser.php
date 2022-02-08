<?php

namespace ZXC\Modules\Auth\Middlewares;

use Exception;
use ZXC\Native\Modules;
use ZXC\Modules\Auth\Auth;
use ZXC\Modules\Auth\User;
use ZXC\Interfaces\IModule;
use ZXC\Interfaces\Psr\Server\MiddlewareInterface;
use ZXC\Modules\Auth\Providers\AuthJwtTokenProvider;
use ZXC\Interfaces\Psr\Server\RequestHandlerInterface;
use ZXC\Interfaces\Psr\Http\Message\ResponseInterface;
use ZXC\Interfaces\Psr\Http\Message\ServerRequestInterface;

class AuthInjectUser implements MiddlewareInterface
{
    /**
     * @var IModule|Auth
     */
    protected $auth = null;

    public function __construct()
    {
        $this->auth = Modules::get('auth');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $loginType = $this->auth->getAuthProvider()->getLoginType();
        if ($loginType === Auth::AUTH_TYPE_JWT) {
            $header = $request->getHeaderLine('Authorization');
            if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
                $user = $this->createUserFromAccessToken($matches[1]);
                if ($user instanceof User) {
                    $request = $request->withAttribute('user', $user);
                    $this->auth->setUser($user);
                }
            }
        }
        return $handler->handle($request);
    }

    /**
     * @param $token
     * @return User|null
     */
    public function createUserFromAccessToken($token)
    {
        try {
            $tokenInfo = $this->auth->getAuthProvider()->decodeToken($token);
            if (isset($tokenInfo['userData'])) {
                /** @var AuthJwtTokenProvider $provider */
                $provider = $this->auth->getAuthProvider();
                $tokens = $provider->getTokenStorage()->fetchTokens($tokenInfo['id']);
                if ($tokens['access_token'] === $token && $tokenInfo['userData']['id'] === $tokens['user_id']) {
                    $userInfo = $this->auth->getStorageProvider()->fetchUserByLogin($tokenInfo['userData']['login']);
                    $permissions = $this->auth->getStorageProvider()->fetchUserPermissions($userInfo['id']);
                    return new ($this->auth->getUserClass())($userInfo['id'], $userInfo['login'], $userInfo['email'], $userInfo['block'], $permissions);
                }
            }
        } catch (Exception $exception) {
            return null;
        }
        return null;
    }
}
