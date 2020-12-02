<?php


namespace ZXC\Modules\Mailer;


use ZXC\Traits\Module;
use ZXC\Interfaces\IModule;
use InvalidArgumentException;
use ZXC\Modules\Mailer\Tx\Mailer;


class Mail extends Mailer implements IModule
{
    use Module;

    protected $version = '0.0.1';
    /**
     * Initialize class with config
     * @param array $config
     * @return bool
     */
    public function init(array $config = null)
    {
        if (empty($config['server']) || empty($config['port']) ||
            empty($config['user']) || empty($config['password']) ||
            empty($config['from']) || empty($config['fromEmail'])) {
            throw new InvalidArgumentException('Incorrect config for SMTP');
        }
        $secure = isset($config['ssl']) ? $config['ssl'] : null;
        $this->setServer($config['server'], $config['port'], $secure)
            ->setAuth($config['user'], $config['password'])
            ->setFrom($config['from'], $config['fromEmail']);
        return true;
    }
}
