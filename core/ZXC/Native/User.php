<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 15/11/2018
 * Time: 00:11
 */

namespace ZXC\Native;

use ZXC\Interfaces\Native\UserService;
use ZXC\Modules\SQL\StructureBaseSQL;
use ZXC\Modules\SQL\StructureControl;

class User implements UserService
{

    protected $id = null;
    protected $email = null;
    protected $token = null;
    /**
     * @var StructureBaseSQL
     */
    protected $userStructure = null;

    public static function find($userId)
    {
        StructureControl::getStructureByName('auth');
        return new User();
    }

}