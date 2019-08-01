<?php

use PHPUnit\Framework\TestCase;
use ZXC\Native\HTTP\CSRF;

/**
 * Created by PhpStorm.
 * User: user
 * Date: 27.11.2018
 * Time: 15:59
 */

class CSRFTest extends TestCase
{

    public function testCSRFCheck()
    {
        $token = CSRF::get();
        $tokenFromHeader = $token;
        $ok = CSRF::check($token, $tokenFromHeader);
        $this->assertTrue($ok);
    }
}