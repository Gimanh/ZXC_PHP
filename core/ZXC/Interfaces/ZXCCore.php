<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 25/06/2018
 * Time: 22:58
 */

namespace ZXC\Interfaces;

interface ZXCCore
{
    /**
     * @param array $config
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function initialize(array $config = []);
}