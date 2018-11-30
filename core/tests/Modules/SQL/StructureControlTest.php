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
        $this->assertSame($structure, $structureByName);

//        $structureByName = StructureControl::getStructureByName($structure['name']);
//        $result = $structureByName->setValues()->exec('select');
    }

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
        $this->expectException(\InvalidArgumentException::class);
        StructureControl::registerStructure($structure);
    }
}