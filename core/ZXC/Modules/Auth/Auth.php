<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 07/09/2018
 * Time: 23:36
 */

namespace ZXC\Modules\Auth;

use ZXC\Modules\SQL\StructureBaseSQL;
use ZXC\Interfaces\Module;
use ZXC\Interfaces\Modules\Auth\Authentication;
use ZXC\Interfaces\Native\DB;
use ZXC\Native\Helper;

/**
 * Class User
 * Все запросы могут использовать класс AuthUser
 * который владеет информацией о ТОКЕНЕ о РОЛИ АВТОРИЗОВАННОГО пользователя
 * умеет проверять доступ к операциям по манипуляциям с контентом
 *
 * @method static bool registration(array $data = null)
 * @method static bool confirmEmail(array $data = null)
 * @method static bool logIn(array $data = null)
 * @method static bool remindPassword(array $data = null)
 * @method static bool changePassword(array $data = null)
 * @method static bool canResetPassword(array $data = null)
 * @method static StructureBaseSQL getStructure()
 * @method static DB getDb()
 * @method static array getConfig()
 *
 * @package ZXC\Modules\Auth
 */
class Auth implements Module
{
    use \ZXC\Traits\Module;
    /**
     * @var Authentication
     */
    protected static $provider = null;

    protected static $config = null;

    public static function __callStatic($method, $args)
    {
        $provider = static::getProvider();
        if (!$provider) {
            throw new \InvalidArgumentException('');
        }
        return call_user_func_array([$provider, $method], $args);
    }

    /**
     * @return Authentication
     */
    public static function getProvider()
    {
        if (!static::$provider) {
            static::$provider = Helper::createInstanceOfClass(static::$config['provider']);
            static::$provider->initialize(static::$config['options']);
            if (!static::$provider) {
                throw new \InvalidArgumentException('ZXC/Modules/Auth does not set');
            }
        }
        return static::$provider;
    }

    /**
     * Initialize class with config
     * @param array $config
     * @return bool
     */
    public function initialize(array $config = null)
    {
        if (!isset($config['provider'])) {
            throw new \InvalidArgumentException('ZXC/Modules/Auth/options/provider does not set');
        }
        static::$config = $config;
        return true;
    }
}