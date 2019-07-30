<?php

use PHPUnit\Framework\TestCase;
use ZXC\Native\Helper;

function callbackHelper($a, $b, $c)
{
    return $a + $b + $c;
}

class Qwe
{
    public function ert($str, $int)
    {
        return $str === 'string1' && $int === 123;
    }

    public static function stErt($str, $int)
    {
        return $str === 'string1' && $int === 123;
    }
}

trait ArgTrait
{

}

class ArgT
{
    use ArgTrait;
    private $arg = null;

    public function __construct($arg)
    {
        $this->arg = $arg;
    }

    /**
     * @return null
     */
    public function getArg()
    {
        return $this->arg;
    }
}

class Abc
{

}

class HelperTest extends TestCase
{
    public function testIsAssoc()
    {
        $array = [1, 2, 3];
        $assocArray = ['field1' => 1, 'field2' => 2];
        $assocArrayWithIndex = ['field' => 1, 2];
        $helper = Helper::getInstance();

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

        $helper = Helper::getInstance();
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

        $helper = Helper::getInstance();
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
        $helper = Helper::getInstance();

        $this->assertFalse($helper::isEmail($invalidEmail1));
        $this->assertFalse($helper::isEmail($invalidEmail1, false));

        $this->assertFalse($helper::isEmail($validEmailInvalidMX));
        $this->assertTrue($helper::isEmail($validEmailInvalidMX, false));

        $this->assertTrue($helper::isEmail($validEmail1));
        $this->assertTrue($helper::isEmail($validEmail1, false));
    }

    public function testGetCleanEmail()
    {
        $helper = Helper::getInstance();
        $validEmail1 = 'headtes()t@gmail.com';
        $this->assertFalse($helper::isEmail($validEmail1));
        $this->assertSame($helper::getCleanEmail($validEmail1), 'headtest@gmail.com');
    }

    public function testGetPasswordHash()
    {
        $password = '12jfHknfjdIldsa*j%';
        $helper = Helper::getInstance();

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

        $helper = Helper::getInstance();
        $this->assertTrue($helper::passwordVerify($password, $hash1));
        $this->assertTrue($helper::passwordVerify($password, $hash2));
        $this->assertFalse($helper::passwordVerify($password, $hashInvalid));
    }

    public function testIsIP()
    {
        $validIP = '192.168.1.0';
        $invalidIP = '1.1.2.3.4';
        $helper = Helper::getInstance();
        $this->assertTrue($helper::isIP($validIP));
        $this->assertFalse($helper::isIP($invalidIP));
    }

    public function testEqual()
    {
        $helper = Helper::getInstance();
        $this->assertTrue($helper::equal('1234', '1234'));
        $this->assertFalse($helper::equal('1234', 1234));
        $this->assertTrue($helper::equal(intval('1234'), 1234));
        $this->assertTrue($helper::equal('1234', strval(1234)));
    }

    public function testCreateHash()
    {
        $helper = Helper::getInstance();
        $this->assertSame(strlen($helper::createHash()), 32);
    }

    public function testGetResponse()
    {
        $helper = Helper::getInstance();
        $this->assertSame($helper::getResponse(500, ['qwerty']), ['status' => 500, 'data' => ['qwerty']]);
    }

    public function testGenerateRandomText()
    {
        $helper = Helper::getInstance();
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

    public function testIssetKeys()
    {
        $arr = [
            'field1' => 'v1',
            'field2' => 'v2',
            'field3' => 'v3',
            'field4' => null,
        ];

        $this->assertTrue(Helper::issetKeys($arr, [
            'field1'
        ]));
        $this->assertTrue(Helper::issetKeys($arr, [
            'field1',
            'field2',
            'field3'
        ]));
        $this->assertTrue(Helper::issetKeys($arr, [
            'field1',
            'field2',
            'field4'
        ]));
        $this->assertFalse(Helper::issetKeys($arr, [
            'field1',
            'field2',
            'field4',
            'field5',
        ]));
    }

    public function testGetConvertedArrayForStructureByKeys()
    {
        $arr = [
            'field1' => 'v1',
            'field2' => 'v2',
            'field3' => 'v3',
            'field4' => null,
        ];
        $r1 = Helper::getConvertedArrayForStructureByKeys($arr, ['field1']);
        $this->assertSame(['field1' => ['value' => 'v1']], $r1);

        $r2 = Helper::getConvertedArrayForStructureByKeys($arr, ['field1', 'field5']);
        $this->assertFalse($r2);

        $r3 = Helper::getConvertedArrayForStructureByKeys($arr, ['field1', 'field2', 'field3']);
        $this->assertSame([
            'field1' => ['value' => 'v1'],
            'field2' => ['value' => 'v2'],
            'field3' => ['value' => 'v3']
        ], $r3);

        $r3 = Helper::getConvertedArrayForStructureByKeys($arr, ['field1', 'field2', 'field3', 'field4']);
        $this->assertSame([
            'field1' => ['value' => 'v1'],
            'field2' => ['value' => 'v2'],
            'field3' => ['value' => 'v3'],
            'field4' => ['value' => null]
        ], $r3);
    }

    public function testFixSlashes()
    {
        $path = 'a' . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'qwerty.js';
        $result = Helper::fixDirectorySlashes('a/b\qwerty.js');
        $this->assertSame($path, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCallCallback()
    {
        $a = 1;
        $b = 2;
        $c = 3;

        $resultGlobalFunc = Helper::callCallback('callbackHelper', $a, $b, $c);
        $this->assertSame(6, $resultGlobalFunc);

        $methodResult = Helper::callCallback('Qwe:ert', 'string1', 123);
        $this->assertTrue($methodResult);

        $methodResult = Helper::callCallback('Qwe:stErt', 'string1', 123);
        $this->assertTrue($methodResult);

        $methodResult = Helper::callCallback(function ($str, $int) {
            return $str === 'string2' && $int === 1212;
        }, 'string1', 123);
        $this->assertFalse($methodResult);

        Helper::callCallback('callbackHelpeasdfasdfkljfasdr', $a, $b, $c);

    }

    public function testIsWritable()
    {
        $writable = ['w', 'w+', 'rw', 'r+', 'x+',
            'c+', 'wb', 'w+b', 'r+b',
            'x+b', 'c+b', 'w+t', 'r+t',
            'x+t', 'c+t', 'a', 'a+'
        ];
//        $readable = [
//            'r', 'w+', 'r+', 'x+', 'c+',
//            'rb', 'w+b', 'r+b', 'x+b',
//            'c+b', 'rt', 'w+t', 'r+t',
//            'x+t', 'c+t', 'a+'];
        foreach ($writable as $item) {
            $result = Helper::isWritable($item);
            $this->assertTrue($result);
        }

        $result = Helper::isWritable('r');
        $this->assertFalse($result);

    }

    /**
     * @method testCreateInstanceOfClass
     * @throws ReflectionException
     */
    public function testCreateInstanceOfClass()
    {
        $instance = Helper::createInstanceOfClass('Qwe');
        $this->assertTrue($instance instanceof Qwe);
    }

    /**
     * @method testCreateInstanceOfClassException
     * @throws ReflectionException
     * @expectedException InvalidArgumentException
     */
    public function testCreateInstanceOfClassException()
    {
        Helper::createInstanceOfClass();
    }

    /**
     * @method testCreateInstanceOfClassWithArguments
     * @throws ReflectionException
     */
    public function testCreateInstanceOfClassWithArguments()
    {
        /**
         * @var ArgT $instance
         */
        $instance = Helper::createInstanceOfClass('ArgT', 123);
        $this->assertSame(123, $instance->getArg());
    }

    /**
     * @method testCreateInstanceOfUndefinedClass
     */
    public function testClassUsesTrait()
    {
        $this->assertTrue(Helper::classUsesTrait('ArgT', 'ArgTrait'));
        $this->assertFalse(Helper::classUsesTrait('Abc', 'ArgTrait'));
    }
}