<?php

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