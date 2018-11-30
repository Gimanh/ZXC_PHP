<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 12/10/2018
 * Time: 23:46
 */

namespace ZXC\Interfaces;


interface ZXC
{
    /**
     * Initialize class with config
     * @param array $config
     * @return bool
     */
    public function initialize(array $config = null);
}