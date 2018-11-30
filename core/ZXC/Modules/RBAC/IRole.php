<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.11.2018
 * Time: 10:00
 */

namespace ZXC\Modules\RBAC;


use ZXC\Interfaces\ZXC;

interface IRole extends ZXC
{
    /**
     * @param $roleId
     * @return IRole
     */
    public function getRolePermissions($roleId);

    public function hasPermission($permissionName);

    /**
     * @return IRolesCollection
     */
    public function getAvailableRoles();

    public function addUserRole($userId, $roleId);

    public function getRoleName();

    public function getUserRoles($userId);
}