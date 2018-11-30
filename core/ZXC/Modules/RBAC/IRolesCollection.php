<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 28/11/2018
 * Time: 19:41
 */

namespace ZXC\Modules\RBAC;


interface IRolesCollection
{
    /**
     * IRolesCollection constructor.
     * @param IRole[] $roles
     */
    public function __construct($roles);

    public function hasRole($roleName);

    public function hasPerm($permName);

    public function getRole($roleName);

    public function count();
}