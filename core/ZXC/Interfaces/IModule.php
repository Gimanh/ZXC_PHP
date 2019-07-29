<?php

namespace ZXC\Interfaces;

interface IModule extends ZXC
{
    public function getModuleName();

    public function getDescription();

    public function getVersion();

    public function getAuthor();

    public function getModuleType();

    public static function create(array $options = null);
}