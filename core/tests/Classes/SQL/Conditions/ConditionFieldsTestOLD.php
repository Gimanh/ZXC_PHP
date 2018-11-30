<?php

use PHPUnit\Framework\TestCase;

class ConditionFieldsTestOLD extends TestCase
{
    public function testGetStringFromFields()
    {
        $stub = $this->getMockForAbstractClass('ZXC\Classes\SQL\Conditions\ConditionFields');
        $stub->expects($this->any())
            ->method('getStringFromFields')
            ->will($this->returnValue(''));
        $this->assertSame($stub->getString(), ' ');
        $this->assertSame($stub->getConditionFields(), []);
    }
}