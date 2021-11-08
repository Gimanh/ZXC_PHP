<?php

namespace ZXC\Modules\Auth\Handlers;

use ZXC\Native\Modules;
use ZXC\Modules\Auth\Auth;
use ZXC\Native\RouteParams;
use ZXC\Native\PSR\ServerRequest;
use ZXC\Modules\Auth\Data\LoginData;
use ZXC\Modules\Auth\Exceptions\InvalidLogin;
use ZXC\Modules\Auth\Exceptions\AuthModuleNotFound;
use ZXC\Interfaces\Psr\Http\Message\ResponseInterface;

class AuthenticationLogin
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
        $loginData = $this->getLoginData($request);
        if (!$loginData) {
            return $response->withStatus(400);
        }
        $loginResult = $this->auth->login($loginData);
        if ($loginResult) {
            return $this->auth->getAuthTypeProvider()->provide($this->auth->getUser()->getInfo(), $response);
        }
        return $response->withStatus(400);
    }

    /**
     * @return false | LoginData
     * @throws InvalidLogin
     */
    public function getLoginData(ServerRequest $request)
    {
        $body = $request->getParsedBody();
        if (!isset($body['login']) || !isset($body['password'])) {
            return false;
        }
        return new LoginData($body['login'], $body['password']);
    }
}
