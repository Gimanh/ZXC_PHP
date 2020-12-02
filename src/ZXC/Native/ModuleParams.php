<?php


namespace ZXC\Native;


class ModuleParams
{
    private $defer;

    private $class = '';

    private $options = [];

    public function __construct(string $class, array $options = [], bool $defer = true)
    {
        $this->class = $class;
        $this->options = $options;
        $this->defer = $defer;
    }
}
