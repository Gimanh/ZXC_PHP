<?php

namespace ZXC\Modules\Auth;

use ZXC\Traits\Module;
use ZXC\Native\CallHandler;
use ZXC\Interfaces\IModule;
use ZXC\Native\PSR\ServerRequest;
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

    const USER_BLOCKED = 1;

    const USER_UNBLOCKED = 0;

    const AUTH_TYPE_JWT = 'jwt';
    /**
     * @var null | AuthStorage
     */
    protected ?AuthStorage $storageProvider = null;

    /**
     * If true confirm email will be sent to user email
     * @var bool
     */
    protected bool $confirmEmail = true;

    /**
     * Handler which will send code to user email
     */
    protected string $codeProvider = 'ZXC\Modules\Auth\Providers\AuthConfirmCodeProvider';

    /**
     * @var null | User
     */
    protected ?User $user = null;

    /**
     * @var null | AuthLoginProvider
     */
    protected ?AuthLoginProvider $authTypeProvider = null;

    /**
     * User class instance of this class will be created
     * @var string
     */
    protected string $userClass = 'ZXC\Modules\Auth\User';

    protected string $confirmUrlTemplate;

    protected string $confirmEmailBody = '{link}';

    /**
     * If true user will be blocked after registration before email will be confirmed
     * @var bool
     */
    protected bool $blockWithoutEmailConfirm = true;

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

        $this->confirmUrlTemplate = $options['email']['confirmUrlTemplate'] ?? null;

        $this->confirmEmailBody = $options['email']['body'];

        $this->blockWithoutEmailConfirm = $options['blockWithoutEmailConfirm'] ?? true;

        $this->authTypeProvider = new $options['authTypeProvider']($options['authTypeProviderOptions'] ?? [])
            ?? new AuthJwtTokenProvider($options['authTypeProviderOptions'] ?? []);

        if (isset($options['userClass'])) {
            $this->userClass = $options['userClass'];
        }
    }

    public function login(LoginData $data): array
    {
        if ($data->isEmail()) {
            $userInfo = $this->storageProvider->fetchUserByEmail($data->getLoginOrEmail());
        } else {
            $userInfo = $this->storageProvider->fetchUserByLogin($data->getLoginOrEmail());
        }
        if ($userInfo && $userInfo['block'] === Auth::USER_UNBLOCKED) {
            if (password_verify($data->getPassword(), $userInfo['password'])) {
                $permissions = $this->storageProvider->fetchUserPermissions($userInfo['id']);
                $this->user = new $this->userClass($userInfo['id'], $userInfo['login'], $userInfo['email'], $userInfo['block'], $permissions);
                return $this->authTypeProvider->login($this->user->getInfo());
            }
        }
        return [];
    }

    public function register(RegisterData $data): array
    {
        $inserted = $this->storageProvider->insertUser($data);
        if ($inserted === AuthStorage::USER_NOT_INSERTED) {
            return ['registration' => false, 'confirmEmail' => false];
        }
        if ($this->confirmEmail && $this->codeProvider) {
            CallHandler::execHandler($this->codeProvider, [$data, $this->confirmUrlTemplate, $this->confirmEmailBody]);
        }
        return ['registration' => true, 'confirmEmail' => $this->confirmEmail && $this->codeProvider];
    }

    public function confirmEmail(ConfirmEmailData $data): bool
    {
        $user = $this->storageProvider->fetchUserByLogin($data->getLogin());
        if ($user) {
            if ($user['login'] === $data->getLogin() && $user['confirm_email_code'] === $data->getCode()) {
                return $this->storageProvider->confirmEmail($data->getLogin(), $data->getCode(), Auth::USER_UNBLOCKED);
            }
        }
        return false;
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

    public function logout(ServerRequest $request): bool
    {
        $header = $request->getHeaderLine('Authorization');
        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $this->authTypeProvider->logout($this->user->getId(), $matches[1]);
        }
        return false;
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

    /**
     * @return string
     */
    public function getUserClass(): string
    {
        return $this->userClass;
    }

    /**
     * @return string
     */
    public function getConfirmUrlTemplate(): string
    {
        return $this->confirmUrlTemplate;
    }

    /**
     * @return bool
     */
    public function isBlockWithoutEmailConfirm(): bool
    {
        return $this->blockWithoutEmailConfirm;
    }
}
