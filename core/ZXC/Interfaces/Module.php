<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 12.11.2018
 * Time: 13:38
 */

namespace ZXC\Interfaces;

interface Module extends ZXC
{
    public function getUID();

    public function getModuleName();

    public function setModuleName($name);

    public function getDescription();

    public function getVersion();

    public function getAuthor();

    public function getModuleType();

    public static function create(array $options = null);
}