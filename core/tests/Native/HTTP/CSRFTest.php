<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 27.11.2018
 * Time: 15:59
 */

class CSRFTest extends \PHPUnit\Framework\TestCase
{
    private $alg = 'HS256';

    public function testCSRFGet()
    {
        $secretKey = '1jnas90)__8y798234j()';
        $result = \ZXC\Native\HTTP\CSRF::get(123, $secretKey, $this->alg);
        $base64 = \ZXC\Native\Helper::base64UrlEncode($result);
        $this->assertSame('kHmvVXi0dkpEBwFzV4VInI0Y16leRo2Z3jWCasjmU3Q', $base64);
        $token = \ZXC\Native\Helper::base64UrlDecode($base64);
        $this->assertSame($result, $token);
    }

    public function testCSRFCheck()
    {
        $secretKey = '1jnas90)__8y798234j()';
        $token = \ZXC\Native\HTTP\CSRF::get(123, $secretKey, $this->alg);
        $ok = \ZXC\Native\HTTP\CSRF::check($token, 123, $secretKey, $this->alg);
        $this->assertTrue($ok);

        $token = \ZXC\Native\HTTP\CSRF::get(1234, $secretKey, $this->alg);
        $false = \ZXC\Native\HTTP\CSRF::check($token, 123, $secretKey, $this->alg);
        $this->assertFalse($false);
    }
}