<?php
/**
 * Basic test case for the Cpanel_Core_Object class
 * @author davidneimeyer
 * @covers Cpanel_Core_Object
 */
class Cpanel_Core_ObjectTest extends PHPUnit_Framework_TestCase
{
    /**
     * Basic instantiation
     */
    public function testCanInstantiateWithoutArgument()
    {
        $cpObj = new Cpanel_Core_Object();
        $this->assertInstanceOf('Cpanel_Core_Object', $cpObj);
        return $cpObj;
    }
    /**
     * Test that it can interate
     * @depends testCanInstantiateWithoutArgument
     */
    public function testCanInterate($fixture)
    {
        $obj = $fixture->getIterator();
        $this->assertInstanceOf('Traversable', $obj);
    }
    /**
     * Basic instantiation with empty array
     */
    public function testCanInstantiateWithEmptyArray()
    {
        $cpObj = new Cpanel_Core_Object(array());
        $this->assertInstanceOf('Cpanel_Core_Object', $cpObj);
        return $cpObj;
    }
    public static $simpleArray = array(
        'a' => 1,
        'b' => 2,
        'c' => 3,
    );
    public static $deepArray = array(
        'a' => array(
            'aa' => array(
                'aaa' => 1
            ),
        ),
        'b' => array(
            'bb' => array(
                'bbb' => 2
            ),
        ),
        'c' => array(
            'cc' => array(
                'ccc' => 3
            ),
        ),
    );
    /**
     * Basic instantiation with simple array
     * 
     */
    public function testCanInstantiateWithSimpleArray()
    {
        $values = self::$simpleArray;
        $cpObj = new Cpanel_Core_Object($values);
        $this->assertInstanceOf('Cpanel_Core_Object', $cpObj);
        return $cpObj;
    }
    /**
     * Basic instantiation with deeply nested array
     */
    public function testCanInstantiateWithDeepArray()
    {
        $values = self::$deepArray;
        $cpObj = new Cpanel_Core_Object($values);
        $this->assertInstanceOf('Cpanel_Core_Object', $cpObj);
        return $cpObj;
    }
    /**
     * Test setOptions method
     * 
     * @depends testCanInstantiateWithoutArgument
     * @paramsCpanel_Core_Object $fixture
     */
    public function testSetOptions($fixture)
    {
        $values = self::$simpleArray;
        $fixture->setOptions($values);
        foreach ($values as $value) {
            $this->assertAttributeContains($value, 'dataContainer', $fixture);
        }
        return $fixture;
    }
    /**
     * Test that when passing a second argument to setOptions
     * you can alter the 'overwrite' behavior and not update existing values. 
     *
     * @depends testSetOptions
     */
    public function testSetOptionsNoOverrideOnRequest()
    {
        $values = self::$simpleArray;
        $localFixture = new Cpanel_Core_Object();
        $localFixture->setOptions($values);
        $callBack = create_function('&$item1, $key', '$item1 .= $item1;');
        array_walk($values, $callBack);
        $localFixture->setOptions($values, false);
        foreach ($values as $value) {
            $this->assertAttributeNotContains($value, 'dataContainer', $localFixture);
        }
        return $localFixture;
    }
    /**
     * Verify setOptions will throw exception if passed non-traversable
     * @expectedException Exception
     */
    public function testSetOptionsThrowsExceptionOnNonTraversableArg()
    {
        $localFixture = new Cpanel_Core_Object();
        $localFixture->setOptions('string');
    }
    /**
     * Verify setOptions will throw exception if $dataContainer is NULL, aka 
     * Cpanel_Core_Object (or classes that inherit it) aren't constructed properly
     * @expectedException Exception
     */
    public function testSetOptionsThrowsExceptionIfInternalPropertyIsNull()
    {
        $stub = $this->getMock('Cpanel_Core_Object', array(
            '__set'
        ));
        $rprop = new ReflectionProperty('Cpanel_Core_Object', 'dataContainer');
        $rprop->setAccessible(true);
        $rprop->setValue($stub, null);
        $stub->setOptions(self::$simpleArray);
    }
    /**
     * Verify quick return for empty traversable
     */
    public function testSetOptionReturnSelfWhenPassedEmptyTraversable()
    {
        $data = self::$simpleArray;
        $mockArrayObject = $this->getMock('ArrayObject', array(
            'count'
        ), array(
            $data
        ));
        $mockArrayObject->expects($this->once())->method('count')->will($this->returnValue(0));
        $cpObj = new Cpanel_Core_Object();
        $cpObjSelf = $cpObj->setOptions($mockArrayObject);
        $this->assertEquals(spl_object_hash($cpObj), spl_object_hash($cpObjSelf));
    }
    /**
     * Test getOption method
     * 
     * Only tests the simplist of data storage schema
     * 
     * @depends testSetOptions
     * @paramsCpanel_Core_Object $fixture
     * @covers  Cpanel_Core_Object::getOption
     */
    public function testGetOption($fixture)
    {
        $values = self::$simpleArray;
        foreach ($values as $key => $evalue) {
            $avalue = $fixture->getOption($key);
            $this->assertEquals($evalue, $avalue);
        }
    }
    /**
     * test getOption method return null on bad key
     * @depends testSetOptions
     * @paramsCpanel_Core_Object $fixture
     */
    public function testGetOptionNonExistentKey($fixture)
    {
        $this->assertNull($fixture->getOption('--fake--'));
    }
    /**
     * Test getAllData method
     * 
     * Only tests the simplist of data storage schema
     * 
     * @depends testSetOptions
     * @paramsCpanel_Core_Object $fixture
     */
    public function testGetAllData($fixture)
    {
        $values = self::$simpleArray;
        $rclass = new ReflectionClass($fixture);
        $rdata = $rclass->getProperty('dataContainer');
        $rdata->setAccessible(true);
        $data = $rdata->getValue($fixture);
        $actual = $fixture->getAllData();
        $this->assertEquals($data, $actual);
        foreach ($values as $key => $value) {
            $this->assertArrayHasKey($key, (array)$data);
            $this->assertContains($value, (array)$data);
        }
    }
    /**
     * Test getAllDataRecursively returns pure array
     * @depends testCanInstantiateWithDeepArray
     * @depends testSetOptions
     */
    public function testGetAllDataRecursively($fixture)
    {
        $actual = $fixture->getAllDataRecursively();
        $expected = self::$deepArray;
        $this->assertEquals($expected, $actual);
    }
    /**
     * Verify setOptions can merge a Cpanel_Core_Object into itself
     * @depends testSetOptions
     * @depends testGetAllDataRecursively
     *          
     */
    public function testSetOptionsCanMergeCpanel_Core_Object()
    {
        $arr1 = array(
            'a' => 1,
            'b' => array(
                'bb' => 2
            )
        );
        $arr2 = array(
            'a' => '11',
            'b' => array(
                'bb' => 22,
                'BB' => 22
            ),
            'c' => 3
        );
        $expected = array(
            'a' => 1,
            'b' => array(
                'bb' => 2,
                'BB' => 22
            ),
            'c' => 3
        );
        $cpObj = new Cpanel_Core_Object($arr2);
        $cpObj->setOptions(new Cpanel_Core_Object($arr1));
        $this->assertEquals($expected, $cpObj->getAllDataRecursively());
    }
    /**
     * Verify setOptions can merge a Cpanel_Core_Object into itself without overwriting
     * @depends testSetOptions
     * @depends testGetAllDataRecursively
     */
    public function testSetOptionsCanMergeCpanelObjectWithoutOverwritting()
    {
        $arr1 = array(
            'a' => 1,
            'b' => array(
                'bb' => 2
            )
        );
        $arr2 = array(
            'a' => '11',
            'b' => array(
                'bb' => 22,
                'BB' => 22
            ),
            'c' => 3
        );
        $expected = array(
            'a' => 11,
            'b' => array(
                'bb' => 22,
                'BB' => 22
            ),
            'c' => 3
        );
        $cpObj = new Cpanel_Core_Object($arr2);
        $cpObj->setOptions(new Cpanel_Core_Object($arr1), false);
        $this->assertEquals($expected, $cpObj->getAllDataRecursively());
    }
    /**
     * test that $cpObj->foo will invoke the __get magic method properly
     */
    public function testMagicGetter()
    {
        $stub = $this->getMock('Cpanel_Core_Object', array(
            '__get'
        ));
        $stub->expects($this->once())->method('__get')->with('foo')->will($this->returnValue('success'));
        $stub->foo;
    }
    /**
     * test that $cpObj->foo will ultimately use $cpObj->getOption('foo')
     */
    public function testMagicGetterUsesGetOption()
    {
        $stub = $this->getMock('Cpanel_Core_Object', array(
            'getOption'
        ));
        $stub->expects($this->once())->method('getOption')->with('foo')->will($this->returnValue('success'));
        $stub->foo;
    }
    /**
     * test that $cpObj->foo = 'bar' will invoke the __set magic method properly
     */
    public function testMagicSetter()
    {
        $stub = $this->getMock('Cpanel_Core_Object', array(
            '__set'
        ));
        $stub->expects($this->once())->method('__set')->with('foo', 'bar');
        $stub->foo = 'bar';
    }
    /**
     * test that $cpObj->foo = 'bar' will ultimately use 
     * $cpObj->setOption(array('foo'=>'bar'))
     */
    public function testMagicSetterUsesSetOptions()
    {
        $stub = $this->getMock('Cpanel_Core_Object', array(
            'setOptions'
        ));
        $stub->expects($this->once())->method('setOptions')->with(array(
            'foo' => 'bar'
        ));
        $stub->foo = 'bar';
    }
}
