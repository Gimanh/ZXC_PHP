<?php

namespace ZXC\Modules\Auth\Handlers;

use ZXC\Native\RouteParams;
use ZXC\Native\PSR\ServerRequest;
use ZXC\Modules\Auth\Providers\AuthJwtTokenProvider;
use ZXC\Interfaces\Psr\Http\Message\ResponseInterface;

class AuthenticationRefreshToken extends BaseAuthHandler
{
    public function __invoke(ServerRequest $request, ResponseInterface $response, RouteParams $routeParams): ResponseInterface
    {
        /** @var $provider AuthJwtTokenProvider */
        $provider = $this->auth->getAuthProvider();
        return $provider->updateTokensByRefreshToken($response, $request->getParsedBody()['refreshToken']);
    }
}
