<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 28/11/2018
 * Time: 19:28
 */

namespace ZXC\Modules\RBAC;


class RolesCollection implements IRolesCollection
{
    /**
     * @var IRole[]
     */
    protected $roles = [];

    /**
     * RolesCollection constructor.
     * @param IRole[] $roles
     */
    public function __construct($roles)
    {
        foreach ($roles as $role) {
            $this->roles[$role->getRoleName()] = $role;
        }
    }

    public function hasRole($roleName)
    {
        return isset($this->roles[$roleName]);
    }

    public function getRole($roleName)
    {
        if ($this->hasRole($roleName)) {
            return $this->roles[$roleName];
        }
        return null;
    }

    public function count()
    {
        return count($this->roles);
    }

    public function hasPerm($permName)
    {
        $has = false;
        foreach ($this->roles as $role) {
            $has = $role->hasPermission($permName);
            if ($has) {
                return true;
            }
        }
        return $has;
    }
}