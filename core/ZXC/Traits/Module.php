<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 13.11.2018
 * Time: 8:31
 */

namespace ZXC\Traits;


trait Module
{
    protected $UID = null;
    protected $author = null;
    protected $version = null;
    protected $moduleType = null;
    protected $moduleName = null;
    protected $description = null;


    public function getUID()
    {
        return $this->UID;
    }

    public function getModuleName()
    {
        return $this->moduleName;
    }

    public function setModuleName($name)
    {
        $this->moduleName = $name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getModuleType()
    {
        return $this->moduleType;
    }

    public static function create(array $options = null)
    {
        $newClass = get_class();
        /**
         * @var $instance \ZXC\Interfaces\Module
         */
        $instance = new $newClass;
        $instance->initialize($options);
        return $instance;
    }
}