<?php

use PHPUnit\Framework\TestCase;
use ZXC\Modules\SQL\StructureControl;

class StructureControlTest extends TestCase
{
    public function testRegisterStructure()
    {
        $structure = [
            'name' => 'userStructure',
            'table' => 'user',
            'fields' => [
                'name' => [

                ],
                'login' => [

                ],
                'password' => [

                ]
            ]
        ];
        StructureControl::registerStructure($structure);
        $structureByName = StructureControl::getStructureByName($structure['name']);
        $this->assertSame($structure['name'], $structureByName['name']);
        $this->assertSame($structure['table'], $structureByName['table']);
        $this->assertSame($structure['fields'], $structureByName['fields']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionRegisterStructureWithoutName()
    {
        $structure = [
            'table' => 'user',
            'fields' => [
                'name' => [

                ],
                'login' => [

                ],
                'password' => [

                ]
            ]
        ];
        StructureControl::registerStructure($structure);
    }
}