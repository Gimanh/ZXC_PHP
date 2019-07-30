<?php

use PHPUnit\Framework\TestCase;

class PasswordHashTest extends TestCase {

    public function testFuncExists() {
        $this->assertTrue(function_exists('password_hash'));
    }

    public function testStringLength() {
        $this->assertEquals(60, strlen(password_hash('foo', PASSWORD_BCRYPT)));
    }

    public function testHash() {
        $hash = password_hash('foo', PASSWORD_BCRYPT);
        $this->assertEquals($hash, crypt('foo', $hash));
    }

    public function testKnownSalt() {
        if((int)phpversion() >= 7){
            error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE );
        }
        $hash = password_hash("rasmuslerdorf", PASSWORD_BCRYPT, array("cost" => 7, "salt" => "usesomesillystringforsalt"));
        $this->assertEquals('$2y$07$usesomesillystringfore2uDLvp1Ii2e./U9C8sBjqp8I90dH6hi', $hash);
    }

    public function testRawSalt() {
        if((int)phpversion() >= 7){
            error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE );
        }
        $hash = password_hash("test", PASSWORD_BCRYPT, array("salt" => "123456789012345678901" . chr(0)));
        if (version_compare(PHP_VERSION, '5.5.0', '<')) {
            $this->assertEquals('$2y$10$KRGxLBS0Lxe3KBCwKxOzLexLDeu0ZfqJAKTubOfy7O/yL2hjimw3u', $hash);
        } else {
            $this->assertEquals('$2y$10$MTIzNDU2Nzg5MDEyMzQ1Nej0NmcAWSLR.oP7XOR9HD/vjUuOj100y', $hash);
        }
    }

    public function testNullBehavior() {
        if((int)phpversion() >= 7){
            error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE );
        }
        $hash = password_hash(null, PASSWORD_BCRYPT, array("salt" => "1234567890123456789012345678901234567890"));
        $this->assertEquals('$2y$10$123456789012345678901uhihPb9QpE2n03zMu9TDdvO34jDn6mO.', $hash);
    }

    public function testIntegerBehavior() {
        if((int)phpversion() >= 7){
            error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE );
        }
        $hash = password_hash(12345, PASSWORD_BCRYPT, array("salt" => "1234567890123456789012345678901234567890"));
        $this->assertEquals('$2y$10$123456789012345678901ujczD5TiARVFtc68bZCAlbEg1fCIexfO', $hash);
    }    

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidAlgo() {
        try{
            password_hash('foo', array());
        }catch (Exception $exception){
            throw new \InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @expectedExceptionMessage password_hash(): Unknown password hashing algorithm: 2
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidAlgo2() {
        try{
            password_hash('foo', 2);
        }catch (Exception $exception){
            throw new \InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidPassword() {
        try{
            password_hash(array(), 1);
        }catch (Exception $exception){
            throw new \InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidSalt() {
        try{
            password_hash('foo', PASSWORD_BCRYPT, array('salt' => array()));
        }catch (Exception $exception){
            throw new \InvalidArgumentException($exception->getMessage());
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidBcryptCostLow() {
        try{
            password_hash('foo', PASSWORD_BCRYPT, array('cost' => 3));
        }catch (Exception $exception){
            throw new \InvalidArgumentException($exception->getMessage());
        }
    }
        
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidBcryptCostHigh() {
        try{
            password_hash('foo', PASSWORD_BCRYPT, array('cost' => 32));
        }catch (Exception $exception){
            throw new \InvalidArgumentException($exception->getMessage());
        }

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidBcryptCostInvalid() {
        try{
            password_hash('foo', PASSWORD_BCRYPT, array('cost' => 'foo'));
        }catch (Exception $exception){
            throw new \InvalidArgumentException($exception->getMessage());
        }

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidBcryptSaltShort() {
        try{
            password_hash('foo', PASSWORD_BCRYPT, array('salt' => 'abc'));
        }catch (Exception $exception){
            throw new \InvalidArgumentException($exception->getMessage());
        }
    }

}
