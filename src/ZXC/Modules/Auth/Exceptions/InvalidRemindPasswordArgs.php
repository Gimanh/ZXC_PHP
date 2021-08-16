<?php

namespace ZXC\Modules\Auth\Exceptions;

use Exception;

class InvalidRemindPasswordArgs extends Exception
{
    protected $message = 'Invalid arguments for remind password.';
}
