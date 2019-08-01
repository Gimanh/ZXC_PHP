<?php
/**
 * Created by PhpStorm.
 * User: nikolaygiman
 * Date: 03/10/2018
 * Time: 18:18
 */

use PHPUnit\Framework\TestCase;

class SomeClass
{
    use \ZXC\Traits\GetSet;
    private $id = 1234567;
    private $classData = [];
    private $filledData = [
        'field' => [
            'subField' => ['name' => 'Jon']
        ],
        'field2' => 'value',
        'field3' => []
    ];

    /**
     * @param array $classData
     * @return SomeClass
     */
    public function setClassData(array $classData)
    {
        $this->classData = $classData;
        return $this;
    }

    /**
     * @return array
     */
    public function getClassData()
    {
        return $this->classData;
    }

    /**
     * @return array
     */
    public function getFilledData()
    {
        return $this->filledData;
    }
}

class GetSetTest extends TestCase
{
    public function testGetValueFromFilledArrayInTraitVar()
    {
        $someClass = new SomeClass();
        $someClass->setStateData([
            'field1' => 'value1',
            'params1' => ['field1' => 'value', 'profile1' => ['name' => 'Nik']]
        ]);
        $result = $someClass->get('field1');
        $this->assertSame('value1', $result);
    }

    public function testGetValueFromFilledArrayInClassVar()
    {
        $someClass = new SomeClass();
        $someClass->setClassData([
            'field1' => 'value1',
            'params1' => ['field1' => 'value', 'profile1' => ['name' => 'Nik']]
        ]);
        $result = $someClass->get('field1', 'classData');
        $this->assertSame('value1', $result);
    }

    public function testGetFromEmptyArray()
    {
        $someClass = new SomeClass();
        $result = $someClass->get();
        $this->assertSame(false, $result);

        $result = $someClass->get('field1');
        $this->assertSame(false, $result);

        $resultFalse = $someClass->get('field1/undefined');
        $this->assertSame(false, $resultFalse);

        $resultFalse = $someClass->get('field1/undefined', '');
        $this->assertSame(false, $resultFalse);
    }

    public function testGetUndefinedValueFromFilledArrayInTrite()
    {
        $someClass = new SomeClass();
        $someClass->setStateData([
            'field1' => 'value1',
            'params1' => ['field1' => 'value', 'profile1' => ['name' => 'Nik']]
        ]);
        $resultFalse = $someClass->get('field1/value1/undefined');
        $this->assertSame(false, $resultFalse);

        $resultFalse = $someClass->get('field1/undefined');
        $this->assertSame(false, $resultFalse);
    }

    public function testGetUndefinedValueFromFilledArrayInClassVar()
    {
        $someClass = new SomeClass();
        $someClass->setClassData([
            'field1' => 'value1',
            'params1' => ['field1' => 'value', 'profile1' => ['name' => 'Nik']]
        ]);
        $resultFalse = $someClass->get('field1/value1/undefined');
        $this->assertSame(false, $resultFalse);

        $resultFalse = $someClass->get('field1/undefined');
        $this->assertSame(false, $resultFalse);
    }

    public function testSetValueForClassVarOneLevel()
    {
        $someClass = new SomeClass();
        $someClass->set('field', [1]);
        $this->assertSame([1], $someClass->get('field'));
    }

    public function testSetValueForClassVarTwoLevel()
    {
        $someClass = new SomeClass();
        $someClass->set('field/sub', [1]);
        $result = $someClass->get('field/sub');
        $this->assertSame([1], $result);
    }

    public function testSetValueForFilledClassVar()
    {
        $someClass = new SomeClass();
        $someClass->set('field/sub', [1], 'filledData');
        $result = $someClass->get('field/sub', 'filledData');
        $this->assertSame([1], $result);

        $resultWithSub = $someClass->getFilledData();
        $filledData = [
            'field' => [
                'subField' => ['name' => 'Jon'],
                'sub' => [1]
            ],
            'field2' => 'value',
            'field3' => []
        ];
        $this->assertSame($filledData, $resultWithSub);
    }

    public function testSetValueForFilledClassVarInExistingKey()
    {
        $someClass = new SomeClass();
        $someClass->set('field/subField/name', 'Nik', 'filledData');
        $result = $someClass->get('field/subField/name', 'filledData');
        $this->assertSame('Nik', $result);

        $resultWithSub = $someClass->getFilledData();
        $filledData = [
            'field' => [
                'subField' => ['name' => 'Nik']
            ],
            'field2' => 'value',
            'field3' => []
        ];
        $this->assertSame($filledData, $resultWithSub);

        $someClass->set('field/subField/name', ['lastName' => 'ln'], 'filledData');
        $filledData = [
            'field' => [
                'subField' => ['name' => ['lastName' => 'ln']]
            ],
            'field2' => 'value',
            'field3' => []
        ];
        $resultWithSub = $someClass->getFilledData();
        $this->assertSame($filledData, $resultWithSub);
    }

    public function testGetNonArrayVars()
    {
        $someClass = new SomeClass();
        $id = $someClass->get('id');
        $this->assertSame(1234567, $id);
    }
}