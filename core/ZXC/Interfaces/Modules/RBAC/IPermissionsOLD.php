<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 22/11/2018
 * Time: 21:51
 */

namespace ZXC\Interfaces\Modules\RBAC;


interface IPermissionsOLD
{
    public function fetchPermissionsByRoleId($roleId);

    public function hasPermission($permissionName);

    public function fetchPermissionsByUserId($userId);

    public function getAvailableRoles();

    public function addUserRole($userId, $roleId);
}