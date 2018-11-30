<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 30/10/2018
 * Time: 22:42
 */

namespace ZXC\Modules\RBAC;


use ZXC\Interfaces\Module;
use ZXC\Traits\StaticProviderAccess;

/**
 * Class RBAC
 * @package ZXC\Modules\RBAC
 * @method static IRole getRolePermissions(int $roleId);
 * @method static hasPermission(string $permissionName);
 * @method static addUserRole($userId, $roleId);
 * @method static getUserRoles($userId);
 * @method static getRoleName();
 * @method static dbResultToRolesCollection();
 */
class RBAC implements Module
{
    use \ZXC\Traits\Module, StaticProviderAccess;

    /**
     * Initialize class with config
     * @param array $config
     * @return bool
     */
    public function initialize(array $config = null)
    {
        if (!$config || !isset($config['provider'])) {
            throw new \InvalidArgumentException('Invalid config');
        }
        static::$config = $config;
        return true;
    }

    protected static function getProviderClass()
    {
        return static::$config['provider'];
    }

    protected static function getConfigForProvider($class)
    {
        if ($class === 'ZXC\Modules\RBAC\Role') {
            return static::$config['options']['role'];
        }
        return null;
    }
}