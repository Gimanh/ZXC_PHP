<?php

namespace ZXC\Modules\Auth;

use ZXC\Modules\Auth\Exceptions\InvalidAuthConfig;
use ZXC\Native\CallHandler;
use ZXC\Traits\Module;
use ZXC\Interfaces\IModule;
use ZXC\Modules\Auth\Data\LoginData;
use ZXC\Modules\Auth\Data\RegisterData;
use ZXC\Modules\Auth\Data\ConfirmEmailData;
use ZXC\Modules\Auth\Data\RemindPasswordData;
use ZXC\Modules\Auth\Data\ChangePasswordData;
use ZXC\Modules\Auth\Data\ChangeRemindedPasswordData;

class Auth implements Authenticable, IModule
{
    use Module;

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
    }

    public function login(LoginData $data)
    {
        // TODO: Implement login() method.
    }

    public function register(RegisterData $data)
    {
        $inserted = $this->storageProvider->insetUser($data);
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
}
