<?php

namespace ZXC\Traits;

trait Module
{
    public function getModuleName()
    {
        return isset($this->moduleName) ? $this->moduleName : null;
    }

    public function getDescription()
    {
        return isset($this->description) ? $this->description : null;
    }

    public function getVersion()
    {
        return isset($this->version) ? $this->version : null;
    }

    public function getAuthor()
    {
        return isset($this->author) ? $this->author : null;
    }

    public function getModuleType()
    {
        return isset($this->moduleType) ? $this->moduleType : null;
    }

    public static function create(array $options = null)
    {
        $newClass = get_class();
        /**
         * @var $instance \ZXC\Interfaces\IModule
         */
        $instance = new $newClass;
        $instance->initialize($options);
        return $instance;
    }
}