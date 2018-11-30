<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 17/10/2018
 * Time: 19:55
 */

namespace ZXC\Modules\Mailer;


use ZXC\Interfaces\Module;
use ZXC\Modules\Mailer\Tx\Mailer;

class Mail extends Mailer implements Module
{
    use \ZXC\Traits\Module;
    /**
     * Initialize class with config
     * @param array $config
     * @return bool
     */
    public function initialize(array $config = null)
    {
        if (empty($config['server']) || empty($config['port']) ||
            empty($config['user']) || empty($config['password']) ||
            empty($config['from']) || empty($config['fromEmail'])) {
            throw new \InvalidArgumentException('Incorrect config for SMTP');
        }
        $secure = isset($config['ssl']) ? $config['ssl'] : null;
        $this->setServer($config['server'], $config['port'], $secure)
            ->setAuth($config['user'], $config['password'])
            ->setFrom($config['from'], $config['fromEmail']);
        return true;
    }
}