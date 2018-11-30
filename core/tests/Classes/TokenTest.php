<?php

use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    public function testEncode()
    {
        $payload = [
            "sub" => "1234567890",
            "name" => "John Doe",
            "iat" => 1516239022
        ];
        $jwt = \ZXC\Classes\Token::encode($payload, 'qwerty', 'HS256');
        $jwtIO = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.350o18fZPeOi3tEGEac6U4UzuB_k-FuZeVQvzf369IQ';
        $this->assertSame($jwtIO, $jwt);

        $payload = [
            "userId" => "1234567890"
        ];
        $jwt = \ZXC\Classes\Token::encode($payload, 'qwerty', 'HS256');
        $jwtIO = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySWQiOiIxMjM0NTY3ODkwIn0.BUiNrGOLLbtb3glBDQ8EczTR40ULSLQyu4_xYLPIqKM';
        $this->assertSame($jwtIO, $jwt);
    }

    /**
     * @throws Exception
     */
    public function testDecode()
    {
        $jwt1 = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.350o18fZPeOi3tEGEac6U4UzuB_k-FuZeVQvzf369IQ';
        $payloadResult1 = \ZXC\Classes\Token::decode($jwt1, 'qwerty');
        $payload1 = [
            "sub" => "1234567890",
            "name" => "John Doe",
            "iat" => 1516239022
        ];
        $this->assertSame($payload1, $payloadResult1);

        $payload2 = [
            "userId" => "1234567890"
        ];
        $jwt2 = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySWQiOiIxMjM0NTY3ODkwIn0.BUiNrGOLLbtb3glBDQ8EczTR40ULSLQyu4_xYLPIqKM';
        $payloadResult2 = \ZXC\Classes\Token::decode($jwt2, 'qwerty');
        $this->assertSame($payloadResult2, $payload2);
    }
}