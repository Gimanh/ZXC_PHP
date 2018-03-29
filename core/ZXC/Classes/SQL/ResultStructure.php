<?php

namespace ZXC\Classes\SQL;

abstract class ResultStructure
{
    abstract public function fillFields($result);
}