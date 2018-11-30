<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 11/10/2018
 * Time: 00:54
 */

namespace ZXC\Interfaces\Native;


use ZXC\Interfaces\ZXC;

interface DB
{
    public function exec($query, array $params = []);

    /**
     * @return string
     */
    public function getDsn();

    /**
     * @return string
     */
    public function getDbType();
}