<?php

use ZXC\Classes\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    /**
     * @method testEncode
     * @throws Exception
     */
    public function testEncode()
    {
        $payload = [
            "sub" => "1234567890",
            "name" => "John Doe",
            "iat" => 1516239022
        ];
        $jwt = Token::encode($payload, 'qwerty', 'HS256');
        $jwtIO = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.350o18fZPeOi3tEGEac6U4UzuB_k-FuZeVQvzf369IQ';
        $this->assertSame($jwtIO, $jwt);

        $payload = [
            "userId" => "1234567890"
        ];
        $jwt = Token::encode($payload, 'qwerty', 'HS256');
        $jwtIO = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VySWQiOiIxMjM0NTY3ODkwIn0.BUiNrGOLLbtb3glBDQ8EczTR40ULSLQyu4_xYLPIqKM';
        $this->assertSame($jwtIO, $jwt);
    }

    /**
     * @method testUrlSafe
     * @throws Exception
     */
    public function testUrlSafe()
    {
        $jwt = Token::encode('url?', '123');
        $this->assertSame('url?', Token::decode($jwt, '123'));
    }

    public function testInvalidJSON()
    {
        $result = Token::jsonDecode('qwerty');
        $this->assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testDecode()
    {
        $jwt1 = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.350o18fZPeOi3tEGEac6U4UzuB_k-FuZeVQvzf369IQ';
        $payloadResult1 = Token::decode($jwt1, 'qwerty');
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
        $payloadResult2 = Token::decode($jwt2, 'qwerty');
        $this->assertSame($payloadResult2, $payload2);
    }

    /**
     * @method testDecodeInvalidSign
     * @throws Exception
     */
    public function testDecodeInvalidSign()
    {
        $jwt1 = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.350o18fZPeOi3tEGEac6U4UzuB_k-FuZeVQvzf369IQ1';
        $decoded = Token::decode($jwt1, 'qwerty');
        $this->assertFalse($decoded);
        $this->assertSame('Failed hash_equals', Token::getReportMessage());
    }

    /**
     * @method testDecodeInvalidNBF
     * @throws Exception
     */
    public function testDecodeNBFLater()
    {
        $secretKey = '123';
        $payload = [
            'role' => 'user',
            'nbf' => time() + 120
        ];
        $jwt = Token::encode($payload, $secretKey);
        $decoded = Token::decode($jwt, $secretKey);
        $this->assertFalse($decoded);
        $this->assertSame(
            'Invalid JWT you can use token after ' . date(DateTime::ISO8601, $payload['nbf']),
            Token::getReportMessage()
        );
    }

    /**
     * @method testDecodeInvalidNBF
     * @throws Exception
     */
    public function testDecodeNBF()
    {
        $secretKey = '123';
        $payload = [
            'role' => 'user',
            'nbf' => time() - 120
        ];
        $jwt = Token::encode($payload, $secretKey);
        $decoded = Token::decode($jwt, $secretKey);
        $this->assertSame($payload, $decoded);
    }

    /**
     * @method testDecodeEXP
     * @throws Exception
     */
    public function testDecodeInvalidEXP()
    {
        $secretKey = '123';
        $payload = [
            'role' => 'user',
            'exp' => time() - 120
        ];
        $jwt = Token::encode($payload, $secretKey);
        $decoded = Token::decode($jwt, $secretKey);
        $this->assertFalse($decoded);
        $this->assertSame('Invalid JWT, token is expired', Token::getReportMessage());
    }

    /**
     * @method testDecodeValidEXP
     * @throws Exception
     */
    public function testDecodeValidEXP()
    {
        $secretKey = '123';
        $payload = [
            'role' => 'user',
            'exp' => time() + 120
        ];
        $jwt = Token::encode($payload, $secretKey);
        $decoded = Token::decode($jwt, $secretKey);
        $this->assertSame($payload, $decoded);
    }

    /**
     * @method testDecodeInvalidIAT
     * @throws Exception
     */
    public function testDecodeInvalidIAT()
    {
        $secretKey = '123';
        $payload = [
            'role' => 'user',
            'iat' => time() + 120
        ];
        $jwt = Token::encode($payload, $secretKey);
        $decoded = Token::decode($jwt, $secretKey);
        $this->assertFalse($decoded);
        $this->assertSame('Invalid JWT iat', Token::getReportMessage());
    }

    /**
     * @method testDecodeInvalidIAT
     * @throws Exception
     */
    public function testDecodeValidIAT()
    {
        $secretKey = '123';
        $payload = [
            'role' => 'user',
            'iat' => time() - 120
        ];
        $jwt = Token::encode($payload, $secretKey);
        $decoded = Token::decode($jwt, $secretKey);
        $this->assertSame($payload, $decoded);
    }

    /**
     * @method testOpenSSLEncodeDecode
     * @throws Exception
     */
    public function testOpenSSLEncodeDecode()
    {
        $privateKey = <<<KEY
-----BEGIN RSA PRIVATE KEY-----
MIIEogIBAAKCAQEAnzyis1ZjfNB0bBgKFMSvvkTtwlvBsaJq7S5wA+kzeVOVpVWw
kWdVha4s38XM/pa/yr47av7+z3VTmvDRyAHcaT92whREFpLv9cj5lTeJSibyr/Mr
m/YtjCZVWgaOYIhwrXwKLqPr/11inWsAkfIytvHWTxZYEcXLgAXFuUuaS3uF9gEi
NQwzGTU1v0FqkqTBr4B8nW3HCN47XUu0t8Y0e+lf4s4OxQawWD79J9/5d3Ry0vbV
3Am1FtGJiJvOwRsIfVChDpYStTcHTCMqtvWbV6L11BWkpzGXSW4Hv43qa+GSYOD2
QU68Mb59oSk2OB+BtOLpJofmbGEGgvmwyCI9MwIDAQABAoIBACiARq2wkltjtcjs
kFvZ7w1JAORHbEufEO1Eu27zOIlqbgyAcAl7q+/1bip4Z/x1IVES84/yTaM8p0go
amMhvgry/mS8vNi1BN2SAZEnb/7xSxbflb70bX9RHLJqKnp5GZe2jexw+wyXlwaM
+bclUCrh9e1ltH7IvUrRrQnFJfh+is1fRon9Co9Li0GwoN0x0byrrngU8Ak3Y6D9
D8GjQA4Elm94ST3izJv8iCOLSDBmzsPsXfcCUZfmTfZ5DbUDMbMxRnSo3nQeoKGC
0Lj9FkWcfmLcpGlSXTO+Ww1L7EGq+PT3NtRae1FZPwjddQ1/4V905kyQFLamAA5Y
lSpE2wkCgYEAy1OPLQcZt4NQnQzPz2SBJqQN2P5u3vXl+zNVKP8w4eBv0vWuJJF+
hkGNnSxXQrTkvDOIUddSKOzHHgSg4nY6K02ecyT0PPm/UZvtRpWrnBjcEVtHEJNp
bU9pLD5iZ0J9sbzPU/LxPmuAP2Bs8JmTn6aFRspFrP7W0s1Nmk2jsm0CgYEAyH0X
+jpoqxj4efZfkUrg5GbSEhf+dZglf0tTOA5bVg8IYwtmNk/pniLG/zI7c+GlTc9B
BwfMr59EzBq/eFMI7+LgXaVUsM/sS4Ry+yeK6SJx/otIMWtDfqxsLD8CPMCRvecC
2Pip4uSgrl0MOebl9XKp57GoaUWRWRHqwV4Y6h8CgYAZhI4mh4qZtnhKjY4TKDjx
QYufXSdLAi9v3FxmvchDwOgn4L+PRVdMwDNms2bsL0m5uPn104EzM6w1vzz1zwKz
5pTpPI0OjgWN13Tq8+PKvm/4Ga2MjgOgPWQkslulO/oMcXbPwWC3hcRdr9tcQtn9
Imf9n2spL/6EDFId+Hp/7QKBgAqlWdiXsWckdE1Fn91/NGHsc8syKvjjk1onDcw0
NvVi5vcba9oGdElJX3e9mxqUKMrw7msJJv1MX8LWyMQC5L6YNYHDfbPF1q5L4i8j
8mRex97UVokJQRRA452V2vCO6S5ETgpnad36de3MUxHgCOX3qL382Qx9/THVmbma
3YfRAoGAUxL/Eu5yvMK8SAt/dJK6FedngcM3JEFNplmtLYVLWhkIlNRGDwkg3I5K
y18Ae9n7dHVueyslrb6weq7dTkYDi3iOYRW8HRkIQh06wEdbxt0shTzAJvvCQfrB
jg/3747WSsf/zBTcHihTRBdAv6OmdhV4/dD5YBfLAkLrd+mX7iE=
-----END RSA PRIVATE KEY-----
KEY;

        $publicKey = <<<KEY
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnzyis1ZjfNB0bBgKFMSv
vkTtwlvBsaJq7S5wA+kzeVOVpVWwkWdVha4s38XM/pa/yr47av7+z3VTmvDRyAHc
aT92whREFpLv9cj5lTeJSibyr/Mrm/YtjCZVWgaOYIhwrXwKLqPr/11inWsAkfIy
tvHWTxZYEcXLgAXFuUuaS3uF9gEiNQwzGTU1v0FqkqTBr4B8nW3HCN47XUu0t8Y0
e+lf4s4OxQawWD79J9/5d3Ry0vbV3Am1FtGJiJvOwRsIfVChDpYStTcHTCMqtvWb
V6L11BWkpzGXSW4Hv43qa+GSYOD2QU68Mb59oSk2OB+BtOLpJofmbGEGgvmwyCI9
MwIDAQAB
-----END PUBLIC KEY-----
KEY;

        $payload = [
            'role' => 'user',
            'iat' => time() - 120
        ];
        $jwt = Token::encode($payload, $privateKey, 'RS256');
        $decodedPayload = Token::decode($jwt, $publicKey);
        $this->assertSame($payload, $decodedPayload);
    }
}