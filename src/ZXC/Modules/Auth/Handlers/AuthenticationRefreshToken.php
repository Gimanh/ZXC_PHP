<?php

namespace ZXC\Modules\Auth\Handlers;

use ZXC\Native\Modules;
use ZXC\Modules\Auth\Auth;
use ZXC\Native\RouteParams;
use ZXC\Native\PSR\ServerRequest;
use ZXC\Modules\Auth\Exceptions\AuthModuleNotFound;
use ZXC\Modules\Auth\Providers\AuthJwtTokenProvider;
use ZXC\Interfaces\Psr\Http\Message\ResponseInterface;

class AuthenticationRefreshToken
{
    /**
     * @var null | Auth
     */
    protected $auth = null;

    /**
     * @throws AuthModuleNotFound
     */
    public function __construct()
    {
        $this->auth = Modules::get('auth');
        if (!$this->auth) {
            throw new AuthModuleNotFound();
        }
    }

    public function __invoke(ServerRequest $request, ResponseInterface $response, RouteParams $routeParams): ResponseInterface
    {
        /** @var $provider AuthJwtTokenProvider */
        $provider = $this->auth->getAuthTypeProvider();
        return $provider->updateTokensByRefreshToken($response, $request->getParsedBody()['refreshToken']);
    }
}
