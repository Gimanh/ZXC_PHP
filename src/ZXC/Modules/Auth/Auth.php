<?php

namespace ZXC\Modules\Auth;

use ZXC\Native\CallHandler;
use ZXC\Traits\Module;
use ZXC\Interfaces\IModule;
use ZXC\Modules\Auth\Data\LoginData;
use ZXC\Modules\Auth\Data\RegisterData;
use ZXC\Modules\Auth\Data\ConfirmEmailData;
use ZXC\Modules\Auth\Data\RemindPasswordData;
use ZXC\Modules\Auth\Data\ChangePasswordData;
use ZXC\Modules\Auth\Exceptions\InvalidAuthConfig;
use ZXC\Modules\Auth\Providers\AuthJwtTokenProvider;
use ZXC\Interfaces\Psr\Http\Message\RequestInterface;
use ZXC\Modules\Auth\Data\ChangeRemindedPasswordData;

class Auth implements Authenticable, IModule
{
    use Module;

    const AUTH_TYPE_JWT = 'jwt';
    /**
     * @var null | AuthStorage
     */
    protected $storageProvider = null;

    /**
     * @var bool
     */
    protected $confirmEmail = true;

    /**
     * Handler which will send code to user
     * @var null
     */
    protected $codeProvider = null;

    /**
     * @var null | User
     */
    protected $user = null;

    /**
     * @var null | AuthLoginProvider
     */
    protected $authTypeProvider = null;

    /**
     * @param array $options
     * @throws InvalidAuthConfig
     */
    public function init(array $options = [])
    {
        if (!isset($options['storageProvider'])) {
            throw new InvalidAuthConfig('Can not get property "storageProvider".');
        }
        $this->storageProvider = new $options['storageProvider']();

        $this->confirmEmail = $options['email']['confirm'] ?? true;

        $this->codeProvider = $options['email']['codeProvider'] ?? null;

        $this->authTypeProvider = new $options['authTypeProvider']($options['authTypeProviderOptions'] ?? [])
            ?? new AuthJwtTokenProvider($options['authTypeProviderOptions'] ?? []);
    }

    public function login(LoginData $data)
    {
        if ($data->isEmail()) {
            $userInfo = $this->storageProvider->fetchUserByEmail($data->getLoginOrEmail());
        } else {
            $userInfo = $this->storageProvider->fetchUserByLogin($data->getLoginOrEmail());
        }
        if ($userInfo) {
            if (password_verify($data->getPassword(), $userInfo['password'])) {
                $permissions = $this->storageProvider->fetchUserPermissions($userInfo['id']);
                $this->user = new User($userInfo['id'], $userInfo['login'], $userInfo['email'], $userInfo['block'], $permissions);
                return true;
            }
        }
        return false;
    }

    public function register(RegisterData $data)
    {
        $inserted = $this->storageProvider->insertUser($data);
        if ($inserted === AuthStorage::USER_NOT_INSERTED) {
            return ['registration' => false, 'confirmEmail' => false];
        }
        if ($this->confirmEmail && $this->codeProvider) {
            $codeData = $data->getData();
            unset($codeData['password']);
            CallHandler::execHandler($this->codeProvider, [$codeData]);
        }
        return ['registration' => true, 'confirmEmail' => $this->confirmEmail && $this->codeProvider];
    }

    public function confirmEmail(ConfirmEmailData $data)
    {
        // TODO: Implement confirmEmail() method.
    }

    public function remindPassword(RemindPasswordData $data)
    {
        // TODO: Implement remindPassword() method.
    }

    public function changeRemindedPassword(ChangeRemindedPasswordData $data)
    {
        // TODO: Implement changeRemindedPassword() method.
    }

    public function changePassword(ChangePasswordData $data)
    {
        // TODO: Implement changePassword() method.
    }

    public function logout()
    {
        // TODO: Implement logout() method.
    }

    public function retrieveFromRequest(RequestInterface $request): UserModel
    {
        // TODO: Implement retrieveFromRequest() method.
    }

    public function getUser(): ?UserModel
    {
        return $this->user;
    }

    /**
     * @return AuthLoginProvider
     */
    public function getAuthTypeProvider(): AuthLoginProvider
    {
        return $this->authTypeProvider;
    }

    /**
     * @return AuthStorage|null
     */
    public function getStorageProvider(): ?AuthStorage
    {
        return $this->storageProvider;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }
}
