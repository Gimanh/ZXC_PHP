<?php


namespace ZXC;


class ZXCFactory
{
    public static function create(string $configFile): ZXC
    {
        return new ZXC($configFile);
    }
}
