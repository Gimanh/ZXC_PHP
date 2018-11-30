<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 28.11.2018
 * Time: 13:41
 */

use \PHPUnit\Framework\TestCase;
use ZXC\Modules\RBAC\RBAC;

class RBACTest extends TestCase
{
    public function test()
    {
        RBAC::getRolePermissions(1);
    }
}