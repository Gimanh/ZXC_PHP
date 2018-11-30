<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 12/10/2018
 * Time: 22:46
 */

namespace ZXC\Interfaces\Modules\Auth;


use ZXC\Interfaces\Native\DB;
use ZXC\Interfaces\ZXC;
use ZXC\Modules\Logger\Logger;
use ZXC\Modules\SQL\StructureBaseSQL;

interface Authentication extends ZXC
{
    /**
     * @param array $data -
     *      [
     *          'email' => 'user@mail.com',
     *          'password'=>'123456'
     *      ]
     * @return bool
     */
    public function logIn(array $data = null);

    /**
     * @param array $data -
     *      [
     *          'email' => 'user@mail.com'
     *      ]
     * @return mixed
     */
    public function logOut(array $data = null);

    /**
     * @param array $data -
     *      [
     *          'email' => 'user@mail.com',
     *          'password' =>'123456'
     *          'passwordRepeat' => '123456'
     *          'agreement' => true
     *      ]
     * @return bool
     */
    public function registration(array $data = null);

    /**
     * @param array $data -
     *      [
     *          'email' => 'user@mail.com'
     *          'code'=> '8cd0c26566a83f4ff5c4439554dd0774'
     *      ]
     * @return bool
     */
    public function confirmEmail(array $data = null);

    /**
     * Reset user password (send email or sms)
     * @param array $data -
     *      [
     *          'email' => 'user@mail.com'
     *      ]
     * @return bool
     */
    public function remindPassword(array $data = null);

    /**
     * Change password in database
     * @param array $data -
     *      [
     *          'email' => 'user@mail.com'
     *          'password' =>'123456'
     *          'passwordRepeat' =>'123456'
     *          'key' =>'78ad0c235272d1352d7e93e660acf2da'
     *      ]
     * @return bool
     */
    public function changePassword(array $data = null);

    /**
     * @return array
     */
    public function getConfig();

    /**
     * @return DB
     */
    public function getDb();

    /**
     * @return StructureBaseSQL
     */
    public function getStructure();

    /**
     * @return Logger
     */
    public function getLogger();
}