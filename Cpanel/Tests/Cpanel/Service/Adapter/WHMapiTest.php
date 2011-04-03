<?php
/**
 * @covers Cpanel_Service_Adapter_WHMapi
 * @author davidneimeyer
 *         
 */
class Cpanel_Service_Adapter_WHMapiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Cpanel_Service_Adapter_WHMapi
     */
    protected $cut = 'Cpanel_Service_Adapter_WHMapi';
    protected $DRFT = 'JSON';
    protected $_validRFT = array(
        'JSON',
        'XML'
    );
    protected $qa = 'Cpanel_Query_Object';
    /**
     * 
     * @param unknown_type                  $methods  
     * @param unknown_type                  $args     
     * @param unknown_type                  $mockName 
     * @param unknown_type                  $callConst
     * @param unknown_type                  $callClone
     * @param unknown_type                  $callA    
     *                                                  
     * @note   This method actually returns a Mock Class
     * 
     * @return Cpanel_Service_Adapter_WHMapi
     */
    public function getA($methods = array(), $args = array(), $mockName = '', $callConst = true, $callClone = true, $callA = true)
    {
        if (empty($methods)) {
            $methods = null;
        }
        $m = $this->getMock($this->cut, $methods, $args, $mockName, $callConst, $callClone, $callA);
        return $m;
    }
    /**
     * @param bool $mock      Return a PHPUnit mock object
     * @param unknown_type        $methods  
     * @param unknown_type        $args     
     * @param unknown_type        $mockName 
     * @param unknown_type        $callConst
     * @param unknown_type        $callClone
     * @param unknown_type        $callA    
     *                                        
     * 
     * @return Cpanel_Query_Object
     */
    public function getRObj($mock = false, $methods = array(), $args = array(), $mockName = '', $callConst = true, $callClone = true, $callA = true)
    {
        if ($mock) {
            if (empty($methods)) {
                $methods = null;
            }
            return $this->getMock($this->qa, $methods, $args, $mockName, $callConst, $callClone, $callA);
        }
        return new Cpanel_Query_Object();
    }
    public function testConstantDRFT()
    {
        $classname = $this->cut;
        $this->assertEquals($this->DRFT, $classname::DRFT);
    }
    public function testPrivateValidRFT()
    {
        $a = new $this->cut();
        $this->assertAttributeEquals($this->_validRFT, '_validRFT', $a);
    }
    public function testConstructArgs()
    {
        $rmeth = new ReflectionMethod($this->cut, '__construct');
        $rargs = $rmeth->getParameters();
        $expected = array(
            'host',
            'user',
            'password',
            'RFT'
        );
        foreach ($rargs as $arg) {
            $actual[$arg->getPosition() ] = $arg->getName();
            $this->assertTrue($arg->isDefaultValueAvailable());
            $this->assertNull($arg->getDefaultValue());
        }
        $this->assertEquals($expected, $actual);
    }
    public function constructVars()
    {
        return array(
            '1.1.1.1', //'host' =>
            'foo', //'user' =>
            'bar', //'password' =>
            'XML', //'RFT' =>
            
        );
    }
    public function rftData()
    {
        return array(
            array(
                'XML',
                0
            ),
            array(
                'JSON',
                0
            ),
            array(
                'blah',
                1
            ),
            array(
                new stdClass(),
                1
            ),
            array(
                array(
                    'XML'
                ),
                1
            ),
        );
    }
    /**
     * @dataProvider rftData
     */
    public function testSetAdapterResponseFormatType($type, $expectE)
    {
        if ($expectE) {
            $this->setExpectedException('Exception');
        }
        $a = $this->getA(null, array(), '', false);
        $a->setAdapterResponseFormatType($type);
    }
    /**
     * @depends testSetAdapterResponseFormatType
     */
    public function testConstructWillSetVars()
    {
        list($h, $u, $p, $RFT) = $this->constructVars();
        $a = new $this->cut($h, $u, $p, $RFT);
        $this->assertAttributeEquals($h, 'host', $a);
        $this->assertAttributeEquals($p, 'auth', $a);
        $this->assertAttributeEquals('pass', 'auth_type', $a);
        $this->assertAttributeEquals($u, 'user', $a);
        $this->assertAttributeEquals($RFT, '_adapterResponseFormatType', $a);
    }
    public function testConstructSanitizesRFT()
    {
        $a = $this->getA(array(
            'setAdapterResponseFormatType'
        ), array(), '', false);
        $a->expects($this->once())->method('setAdapterResponseFormatType');
        $a->__construct();
    }
    /**
     * @depends testConstantDRFT
     */
    public function testConstructWillSeRFTByDefault()
    {
        $a = new $this->cut();
        $this->assertAttributeEquals($this->DRFT, '_adapterResponseFormatType', $a);
    }
    public function testGetAdapterResponseFormatTypeFetchesStored()
    {
        list($h, $u, $p, $RFT) = $this->constructVars();
        $expected = 'blah';
        $a = new $this->cut($h, $u, $p, $RFT);
        $rprop = new ReflectionProperty($a, '_adapterResponseFormatType');
        $rprop->setAccessible(true);
        $rprop->setValue($a, $expected);
        $actual = $a->getAdapterResponseFormatType();
        $this->assertEquals($expected, $actual);
    }
    public function testLegacyXmlapiUnderscoreQueryDefersToMakeQuery()
    {
        $func = 'test';
        $args = array(
            'blah'
        );
        $a = $this->getA(array(
            'makeQuery'
        ));
        $a->expects($this->once())->method('makeQuery')->with($func, $args)->will($this->returnValue('foo'));
        $r = $a->xmlapi_query($func, $args);
        $this->assertEquals('foo', $r);
    }
    public function apiQueryData()
    {
        return array(
            array(
                'foo',
                'bar',
                'baz',
                '',
                0
            ),
            array(
                '',
                'bar',
                'baz',
                '',
                1
            ),
            array(
                'foo',
                '',
                'baz',
                '',
                1
            ),
            array(
                'foo',
                'bar',
                '',
                '',
                1
            ),
            array(
                'foo',
                'bar',
                'baz',
                'string',
                1
            ),
            array(
                'foo',
                'bar',
                'baz',
                new stdClass(),
                1
            ),
            array(
                'foo',
                'bar',
                'baz',
                array(
                    'one',
                    'two'
                ),
                0
            ),
        );
    }
    public function apiQueryData1()
    {
        return array(
            array(
                'foo',
                'bar',
                'baz',
                '',
            ),
            array(
                'foo',
                'bar',
                'baz',
                array(
                    'one',
                    'two'
                )
            ),
        );
    }
    public function apiQueryData2()
    {
        return array(
            array(
                'foo',
                'bar',
                'baz',
                '',
            ),
            array(
                'foo',
                'bar',
                'baz',
                array(
                    'one' => 'one',
                    'two' => 'two'
                ),
            ),
        );
    }
    /**
     * @depends      testConstructWillSetVars
     * @dataProvider apiQueryData
     * @paramsunknown_type $user   
     * @paramsunknown_type $mod    
     * @paramsunknown_type $func   
     * @paramsunknown_type $args   
     * @paramsunknown_type $expectE
     */
    public function testLegacyApi1UnderscoreQueryRequiresInput($user = '', $mod = '', $func = '', $args = '', $expectE = 0)
    {
        if ($expectE) {
            $this->setExpectedException('Exception');
        }
        $a = $this->getA(array(
            'makeQuery'
        ));
        $rObj = $this->getRObj();
        $a->setResponseObject($rObj);
        $a->api1_query($user, $mod, $func, $args);
    }
    /**
     * @depends      testConstructWillSetVars
     * @dataProvider apiQueryData
     * @paramsunknown_type $user   
     * @paramsunknown_type $mod    
     * @paramsunknown_type $func   
     * @paramsunknown_type $args   
     * @paramsunknown_type $expectE
     */
    public function testLegacyApi2UnderscoreQueryRequiresInput($user = '', $mod = '', $func = '', $args = '', $expectE = 0)
    {
        if ($expectE) {
            $this->setExpectedException('Exception');
        }
        $a = $this->getA(array(
            'makeQuery'
        ));
        $rObj = $this->getRObj();
        $a->setResponseObject($rObj);
        $a->api2_query($user, $mod, $func, $args);
    }
    /**
     * @depends      testLegacyApi2UnderscoreQueryRequiresInput
     * @dataProvider apiQueryData2
     * @paramsunknown_type $user   
     * @paramsunknown_type $module 
     * @paramsunknown_type $func   
     * @paramsunknown_type $args   
     * @paramsunknown_type $expectE
     */
    public function testLegacyApi2UnderscoreQuerySetsURLParams($user = '', $module = '', $func = '', $args = array())
    {
        if (empty($args)) {
            $args = array();
            $eargs = array();
        } else {
            $eargs = $args;
        }
        $cpArgType = $this->DRFT;
        foreach (array(
            'module',
            'func',
            'user'
        ) as $var) {
            $eargs['cpanel_' . strtolower($cpArgType) . 'api_' . $var] = $$var;
        }
        $eargs['cpanel_' . strtolower($cpArgType) . 'api_apiversion'] = '2';
        $a = $this->getA(array(
            'makeQuery'
        ));
        $a->expects($this->once())->method('makeQuery')->with('cpanel', $eargs);
        $rObj = $this->getRObj();
        $rObj->setResponseFormatType($cpArgType);
        $a->setResponseObject($rObj);
        $a->api2_query($user, $module, $func, $args);
    }
    /**
     * @depends      testLegacyApi1UnderscoreQueryRequiresInput
     * @dataProvider apiQueryData1
     * @paramsunknown_type $user   
     * @paramsunknown_type $module 
     * @paramsunknown_type $func   
     * @paramsunknown_type $args   
     * @paramsunknown_type $expectE
     */
    public function testLegacyApi1UnderscoreQuerySetsURLParams($user = '', $module = '', $func = '', $args = array())
    {
        if (empty($args)) {
            $args = array();
        }
        $eargs = array();
        $cpArgType = $this->DRFT;
        foreach (array(
            'module',
            'func',
            'user'
        ) as $var) {
            $eargs['cpanel_' . strtolower($cpArgType) . 'api_' . $var] = $$var;
        }
        $eargs['cpanel_' . strtolower($cpArgType) . 'api_apiversion'] = '1';
        foreach ($args as $key => $value) {
            $eargs['arg-' . $key] = $value;
        }
        $a = $this->getA(array(
            'makeQuery'
        ));
        $a->expects($this->once())->method('makeQuery')->with('cpanel', $eargs);
        $rObj = $this->getRObj();
        $rObj->setResponseFormatType($cpArgType);
        $a->setResponseObject($rObj);
        $a->api1_query($user, $module, $func, $args);
    }
}
