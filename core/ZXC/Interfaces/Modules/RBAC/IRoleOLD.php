<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 22/11/2018
 * Time: 21:50
 */

namespace ZXC\Interfaces\Modules\RBAC;


interface IRoleOLD
{
    public function fetchUserRolesById($userId);

    public function hasRole($roleName);

    public function getRoleId($roleName);
}