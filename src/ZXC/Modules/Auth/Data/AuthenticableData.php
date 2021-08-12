<?php


namespace ZXC\Modules\Auth\Data;


use InvalidArgumentException;


interface AuthenticableData
{
    /**
     * @return bool
     * @throws InvalidArgumentException
     */
    public function validate(): bool;

}
