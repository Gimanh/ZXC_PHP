<?php

namespace ZXC\Traits;

trait GetSet
{
    private $stateData = [];

    /**
     * @param string $property 'params/someData'
     * @param string $from 'post'
     * @return bool|mixed
     */
    public function get($property = '', $from = 'stateData')
    {
        if (!$property || !property_exists($this, $from)) {
            return false;
        }
        $pathLocal = explode('/', $property);
        if (empty($this->$from) || !is_array($this->$from)) {
            if (property_exists($this, $property)) {
                return $this->$property;
            }
            return false;
        }
        $configParameters = $this->$from;
        foreach ($pathLocal as $item) {
            if (is_array($configParameters) && array_key_exists($item, $configParameters)) {
                $configParameters = $configParameters[$item];
            } else {
                return false;
            }
        }
        return $configParameters;
    }

    /**
     * @param string $path
     * @param null $value
     * @param string $property
     * @return string
     */
    public function set($path = '', $value = null, $property = 'stateData')
    {
        if (!$path) {
            return false;
        }
        if (property_exists($this, $property)) {
            $pathLocal = explode('/', $path);
            if (!is_array($this->$property)) {
                if (empty($this->$property)) {
                    $this->$property = [];
                } else {
                    return false;
                }
            }
            $configParameters = &$this->$property;
            foreach ($pathLocal as $item) {
                if (array_key_exists($item, $configParameters)) {
                    $configParameters = &$configParameters[$item];
                } else {
                    $configParameters[$item] = [];
                    $configParameters = &$configParameters[$item];
                }
            }
            $configParameters = $value;
            return true;
        } else {
            return false;
        }
    }

    public function setStateData(array $data = [])
    {
        $this->stateData = $data;
    }
}