<?php

use PHPUnit\Framework\TestCase;

$dir = __DIR__;
$config = [];
$file = $dir . '/../index.php';
if (file_exists($file)) {
    require_once $file;
}

class HelperTest extends TestCase
{
    public function testIsAssoc()
    {
        $array = [1, 2, 3];
        $assocArray = ['field1' => 1, 'field2' => 2];
        $assocArrayWithIndex = ['field' => 1, 2];
        $helper = \ZXC\Native\Helper::getInstance();

        $this->assertFalse($helper::isAssoc($array));
        $this->assertTrue($helper::isAssoc($assocArray));
        $this->assertTrue($helper::isAssoc($assocArrayWithIndex));
    }

    public function testIsValidLogin()
    {
        $validLogin1 = 'testLogin123';
        $validLogin2 = 'qwer';
        $validLogin3 = 'qewrdfsrdgfhdgftrgfde';

        $invalidLogin1 = 'qwe';
        $invalidLogin2 = 'qewrdfsrdgfhdgftrgfdeq';
        $invalidLogin3 = '12312313';

        $helper = \ZXC\Native\Helper::getInstance();
        $this->assertTrue($helper::isValidLogin($validLogin1));
        $this->assertTrue($helper::isValidLogin($validLogin2));
        $this->assertTrue($helper::isValidLogin($validLogin3));
        $this->assertFalse($helper::isValidLogin($invalidLogin3));
        $this->assertFalse($helper::isValidLogin($invalidLogin1));
        $this->assertFalse($helper::isValidLogin($invalidLogin2));
    }

    public function testIsValidStrongPassword()
    {
        $validPassword1 = 'qwerty1Q';
        $validPassword2 = '12jfHknfjdIldsa*j%';

        $invalidPassword1 = '12345678';
        $invalidPassword2 = 'asdfasdfasdfa';
        $invalidPassword3 = 'ASDFASDFASDFASDFA';
        $invalidPassword4 = 'ASDFASDF12345';
        $invalidPassword5 = 'sdfaasdfasdf12345';

        $helper = \ZXC\Native\Helper::getInstance();
        $this->assertTrue($helper::isValidStrongPassword($validPassword1));
        $this->assertTrue($helper::isValidStrongPassword($validPassword2));

        $this->assertFalse($helper::isValidStrongPassword($invalidPassword1));
        $this->assertFalse($helper::isValidStrongPassword($invalidPassword2));
        $this->assertFalse($helper::isValidStrongPassword($invalidPassword3));
        $this->assertFalse($helper::isValidStrongPassword($invalidPassword4));
        $this->assertFalse($helper::isValidStrongPassword($invalidPassword5));
    }

    public function testIsEmail()
    {
        $invalidEmail1 = 'asdfasdfasdfas@fdsafsdfasd';
        $validEmailInvalidMX = 'asdfasdfasdfas@fdsafsdfasd.r';
        $validEmail1 = 'headtest@gmail.com';
        $helper = \ZXC\Native\Helper::getInstance();

        $this->assertFalse($helper::isEmail($invalidEmail1));
        $this->assertFalse($helper::isEmail($invalidEmail1, false));

        $this->assertFalse($helper::isEmail($validEmailInvalidMX));
        $this->assertTrue($helper::isEmail($validEmailInvalidMX, false));

        $this->assertTrue($helper::isEmail($validEmail1));
        $this->assertTrue($helper::isEmail($validEmail1, false));
    }

    public function testGetCleanEmail()
    {
        $helper = \ZXC\Native\Helper::getInstance();
        $validEmail1 = 'headtes()t@gmail.com';
        $this->assertFalse($helper::isEmail($validEmail1));
        $this->assertSame($helper::getCleanEmail($validEmail1), 'headtest@gmail.com');
    }

    public function testGetPasswordHash()
    {
        $password = '12jfHknfjdIldsa*j%';
        $helper = \ZXC\Native\Helper::getInstance();

        $passwordHash1 = $helper::getPasswordHash($password);
        $passwordHash2 = $helper::getPasswordHash($password);

        $this->assertSame(strlen($passwordHash1), 60);
        $this->assertSame(strlen($passwordHash2), 60);
    }

    public function testPasswordVerify()
    {
        $password = '12jfHknfjdIldsa*j%';
        $hash1 = '$2y$10$OFyD8zzF0UC7iSVtojW5k.Ol58Gw6PQEGWEIf9mDv08uXzL1knp6W';
        $hash2 = '$2y$10$7eemW28ULBAVQseqw.GOju5GqWrOhTMkQhyEGJwK8ewe.lEeV8q6G';
        $hashInvalid = '$2y$10$7eemW12ULBAVQseqw.GOju5GqWrOhTMkQhyEGJwK8ewe.lEeV8q6G';

        $helper = \ZXC\Native\Helper::getInstance();
        $this->assertTrue($helper::passwordVerify($password, $hash1));
        $this->assertTrue($helper::passwordVerify($password, $hash2));
        $this->assertFalse($helper::passwordVerify($password, $hashInvalid));
    }

    public function testIsIP()
    {
        $validIP = '192.168.1.0';
        $invalidIP = '1.1.2.3.4';
        $helper = \ZXC\Native\Helper::getInstance();
        $this->assertTrue($helper::isIP($validIP));
        $this->assertFalse($helper::isIP($invalidIP));
    }

    public function testEqual()
    {
        $helper = \ZXC\Native\Helper::getInstance();
        $this->assertTrue($helper::equal('1234', '1234'));
        $this->assertFalse($helper::equal('1234', 1234));
        $this->assertTrue($helper::equal(intval('1234'), 1234));
        $this->assertTrue($helper::equal('1234', strval(1234)));
    }

    public function testCreateHash()
    {
        $helper = \ZXC\Native\Helper::getInstance();
        $this->assertSame(strlen($helper::createHash()), 32);
    }

    public function testGetResponse()
    {
        $helper = \ZXC\Native\Helper::getInstance();
        $this->assertSame($helper::getResponse(500, ['qwerty']), ['status' => 500, 'data' => ['qwerty']]);
    }

    public function testGenerateRandomText()
    {
        $helper = \ZXC\Native\Helper::getInstance();
        $resultText = $helper::generateRandomText(3, 8, true, ['a']);

        $this->assertTrue(strlen($resultText) >= 3);
        $this->assertTrue(strlen($resultText) <= 8);

        $alphabetIgnore = [
            'A',
            'b',
            'B',
            'c',
            'C',
            'd',
            'D',
            'e',
            'E',
            'f',
            'F',
            'g',
            'G',
            'h',
            'H',
            'i',
            'I',
            'j',
            'J',
            'k',
            'K',
            'l',
            'L',
            'm',
            'M',
            'n',
            'N',
            'o',
            'O',
            'p',
            'P',
            'q',
            'Q',
            'r',
            'R',
            's',
            'S',
            't',
            'T',
            'u',
            'U',
            'v',
            'V',
            'w',
            'W',
            'z',
            'Z',
            'Y',
            'y',
            'x',
            'X',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '0'
        ];
        $resultTextWithIgnore = $helper::generateRandomText(8, 8, false, $alphabetIgnore);
        $this->assertSame($resultTextWithIgnore, 'aaaaaaaa');

        $alphabetIgnore = [
            'a',
            'A',
            'b',
            'B',
            'c',
            'C',
            'd',
            'D',
            'e',
            'E',
            'f',
            'F',
            'g',
            'G',
            'h',
            'H',
            'i',
            'I',
            'j',
            'J',
            'k',
            'K',
            'l',
            'L',
            'm',
            'M',
            'n',
            'N',
            'o',
            'O',
            'p',
            'P',
            'q',
            'Q',
            'r',
            'R',
            's',
            'S',
            't',
            'T',
            'u',
            'U',
            'v',
            'V',
            'w',
            'W',
            'z',
            'Z',
            'Y',
            'y',
            'x',
            'X',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
            '0'
        ];
        $resultTextWithIgnore = $helper::generateRandomText(7, 7, false, $alphabetIgnore);
        $this->assertSame($resultTextWithIgnore, '1111111');
    }
}