<?php
class customParser extends Cpanel_Core_Object implements Cpanel_Parser_Interface
{
    public function canParse($type)
    {
    }
    public function parse($str)
    {
    }
    public function encodeQueryObject($obj)
    {
    }
    public function getParserInternalErrors($prefix = '', $default = '')
    {
    }
}
class Cpanel_Query_ObjectTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Cpanel_Query_Live_Abstract
     */
    protected $qa;
    protected $cut = 'Cpanel_Query_Object';
    protected $p = 'Cpanel_Parser_JSON';
    protected $pType = 'JSON';
    protected $customParser = 'customParser';
    /**
     * 
     * Enter description here ...
     * @param unknown_type        $methods  
     * @param unknown_type        $args     
     * @param unknown_type        $mockName 
     * @param unknown_type        $callConst
     * @param unknown_type        $callClone
     * @param unknown_type        $callA    
     *                                        
     * @note   This method actually returns a Mock Class
     * 
     * @return Cpanel_Query_Object
     */
    public function getRObj($methods = array(), $args = array(), $mockName = '', $callConst = true, $callClone = true, $callA = true)
    {
        if (empty($methods)) {
            $methods = null;
        }
        $m = $this->getMock($this->cut, $methods, $args, $mockName, $callConst, $callClone, $callA);
        return $m;
    }
    public function getParser($mock = false, $methods = array(), $args = array(), $mockName = '', $callConst = true, $callClone = true, $callA = true)
    {
        if ($mock) {
            if (empty($methods)) {
                $methods = null;
            }
            return $this->getMock($this->p, $methods, $args, $mockName, $callConst, $callClone, $callA);
        }
        return new $this->p();
    }
    public function getCustomParser()
    {
        return new $this->customParser();
    }
    /**
     * array of properties: $name, $visibility, $value after instantiation
     */
    public function classProperties()
    {
        return array(
            array(
                '_query',
                2,
                new Cpanel_Core_Object()
            ),
            array(
                '_response',
                2,
                new Cpanel_Core_Object()
            ),
            array(
                '_rawResponse',
                2,
                null
            ),
            array(
                '_pinterface',
                2,
                'Cpanel_Parser_Interface'
            ),
            array(
                '_inputFormat',
                2,
                ''
            ),
            array(
                '_defaultOutputFormat',
                2,
                'Cpanel_Core_Object'
            ),
            array(
                '_outputFormat',
                2,
                'Cpanel_Core_Object'
            ),
            array(
                '_responseFormat',
                2,
                ''
            ),
            array(
                '_responseParser',
                2,
                ''
            ),
            array(
                '_responseErrors',
                2,
                array()
            ),
        );
    }
    public function getRClassProperty($name, $class = '')
    {
        try {
            if (empty($class)) {
                $class = $this->cut;
            }
            $prop = new ReflectionProperty($class, $name);
            return $prop;
        }
        catch(Exception $e) {
            $this->fail("Property {$name} for {$class} is not defined.");
        }
    }
    public function testCanInstantiateClean()
    {
        $this->assertInstanceOf($this->cut, new $this->cut());
    }
    /**
     * @dataProvider classProperties
     * @depends      testCanInstantiateClean
     *               
     * @paramsunknown_type $name        
     * @paramsunknown_type $visibility  
     * @paramsunknown_type $defaultValue Value after instantiation
     */
    public function testQueryObjectProperties($name, $visibility, $defaultValue = '')
    {
        $rObj = new $this->cut();
        $prop = $this->getRClassProperty($name, $rObj);
        switch ($visibility) {
        case 0:
            $this->assertTrue($prop->isPublic());
            break;

        case 1:
            $this->assertTrue($prop->isProtected());
            break;

        case 3:
            $this->assertTrue($prop->isPrivate());
            break;
        }
        if (is_object($defaultValue)) {
            $this->assertAttributeInstanceOf(get_class($defaultValue), $name, $rObj);
        } elseif ($defaultValue !== '') {
            $this->assertAttributeEquals($defaultValue, $name, $rObj);
        }
    }
    public function testSetResponseFormatTypeStoresFT()
    {
        $rObj = $this->getRObj(array(
            'setResponseParser',
            'getValidParser'
        ));
        $p = $this->getParser(true, array(
            'canParse'
        ));
        $rObj->expects($this->any())->method('setResponseParser')->will($this->returnValue($this->anything()));
        $rObj->expects($this->any())->method('getValidParser')->will($this->returnValue($p));
        $p->expects($this->any())->method('canParse')->will($this->returnValue(true));
        $rprop = new ReflectionProperty($this->cut, '_responseParser');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, $p);
        $rObj->setResponseFormatType('foo');
        $rprop = new ReflectionProperty($this->cut, '_responseFormat');
        $rprop->setAccessible(true);
        $this->assertEquals('foo', $rprop->getValue($rObj));
    }
    public function testSetResponseFormatTypeAttemptToReusePreviouslyStoredParser()
    {
        $rObj = $this->getRObj(array(
            'setResponseParser',
            'getValidParser'
        ));
        $p = $this->getParser(true, array(
            'canParse'
        ));
        $rObj->expects($this->any())->method('setResponseParser')->will($this->returnValue($this->anything()));
        $rObj->expects($this->any())->method('getValidParser')->will($this->returnValue($p));
        $p->expects($this->once())->method('canParse')->will($this->returnValue(true));
        $rprop = new ReflectionProperty($this->cut, '_responseParser');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, $p);
        $rObj->setResponseFormatType('foo');
    }
    /**
     * @expectedException Exception
     */
    public function testSetResponseFormatTypeThrowForPreviouslyStoredParserCanNotParse()
    {
        $rObj = $this->getRObj(array(
            'setResponseParser',
            'getValidParser'
        ));
        $p = $this->getParser(true, array(
            'canParse'
        ));
        $rObj->expects($this->any())->method('setResponseParser')->will($this->returnValue($this->anything()));
        $rObj->expects($this->any())->method('getValidParser')->will($this->returnValue($p));
        $p->expects($this->once())->method('canParse')->will($this->returnValue(false));
        $rprop = new ReflectionProperty($this->cut, '_responseParser');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, $p);
        $rObj->setResponseFormatType('foo');
    }
    /**
     * @expectedException Exception
     */
    public function testSetResponseFormatTypeThrowsOnBadInterface()
    {
        $rObj = $this->getRObj(array(
            'setResponseParser',
            'getValidParser'
        ));
        $p = $this->getParser(true, array(
            'canParse'
        ));
        $rObj->expects($this->any())->method('setResponseParser')->will($this->returnValue($this->anything()));
        $rObj->expects($this->any())->method('getValidParser')->will($this->returnValue($p));
        $p->expects($this->any())->method('canParse')->will($this->returnValue(true));
        $rprop = new ReflectionProperty($this->cut, '_responseParser');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, new stdClass());
        $rObj->setResponseFormatType('foo');
    }
    public function testSetResponseFormatTypeWillSetFreshParser()
    {
        $rObj = $this->getRObj(array(
            'setResponseParser',
            'getValidParser'
        ));
        $p = $this->getParser(true, array(
            'canParse'
        ));
        $rObj->expects($this->once())->method('setResponseParser')->with($p)->will($this->returnValue($this->anything()));
        $rObj->expects($this->any())->method('getValidParser')->with('foo')->will($this->returnValue($p));
        $p->expects($this->never())->method('canParse')->will($this->returnValue(true));
        $rObj->setResponseFormatType('foo');
    }
    public function testSetResponseFormatTypeWillSetFreshParserOnRequest()
    {
        $rObj = $this->getRObj(array(
            'setResponseParser',
            'getValidParser'
        ));
        $p = $this->getParser(true, array(
            'canParse'
        ));
        $rObj->expects($this->once())->method('setResponseParser')->with($p)->will($this->returnValue($this->anything()));
        $rObj->expects($this->any())->method('getValidParser')->with('foo')->will($this->returnValue($p));
        $p->expects($this->never())->method('canParse')->will($this->returnValue(true));
        $rprop = new ReflectionProperty($this->cut, '_responseParser');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, new stdClass());
        $rObj->setResponseFormatType('foo', true);
    }
    public function testGetValidParserWillLoadShippedParser()
    {
        $rObj = new $this->cut();
        $p = $this->getParser();
        $actual = $rObj->getValidParser($this->pType);
        $this->assertInstanceOf(get_class($p), $actual);
    }
    public function testGetValidParserWillLoadCustomParser()
    {
        $rObj = new $this->cut();
        $p = $this->getCustomParser();
        //need to suppress warnings as the Zend autoload with call include_once()
        //at will, without a file_exists (an E_WARNING if computed file is absent)
        // exceptions should still be throw plus and any require() statement will
        // trigger E_ERROR which will stop script execution ;) should be pretty
        // safe and reliable
        $actual = @$rObj->getValidParser(get_class($p));
        $this->assertInstanceOf(get_class($p), $actual);
    }
    /**
     * @expectedException Exception
     */
    public function testGetValidParserThrowOnBadCustomParser()
    {
        $rObj = new $this->cut();
        $p = 'fooBarBaz';
        //need to suppress warnings as the Zend autoload with call include_once()
        //at will, without a file_exists (an E_WARNING if computed file is absent)
        // exceptions should still be throw plus and any require() statement will
        // trigger E_ERROR which will stop script execution ;) should be pretty
        // safe and reliable
        $actual = @$rObj->getValidParser($p);
    }
    /**
     * @expectedException Exception
     */
    public function testGetValidParserThrowOnCustomParserWhichDoesNotImplementParserInterface()
    {
        $rObj = new $this->cut();
        $p = 'stdClass';
        //need to suppress warnings as the Zend autoload with call include_once()
        //at will, without a file_exists (an E_WARNING if computed file is absent)
        // exceptions should still be throw plus and any require() statement will
        // trigger E_ERROR which will stop script execution ;) should be pretty
        // safe and reliable
        $actual = @$rObj->getValidParser($p);
    }
    public function testGetValidParserValidatesCustomParserBasedOnPrivatePinterface()
    {
        $rObj = new $this->cut();
        $p = new ArrayObject();
        $rprop = new ReflectionProperty($this->cut, '_pinterface');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, 'Countable');
        $rprop = new ReflectionProperty($this->cut, '_pinterface');
        $rprop->setAccessible(true);
        //need to suppress warnings as the Zend autoload with call include_once()
        //at will, without a file_exists (an E_WARNING if computed file is absent)
        // exceptions should still be throw plus and any require() statement will
        // trigger E_ERROR which will stop script execution ;) should be pretty
        // safe and reliable
        $actual = @$rObj->getValidParser(get_class($p));
        $this->assertInstanceOf(get_class($p), $actual);
    }
    public function testSetResponseParserStoresShippedParser()
    {
        $rObj = $this->getRObj();
        $p = $this->getParser();
        $rprop = new ReflectionProperty($this->cut, '_responseParser');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, new stdClass());
        $rprop = new ReflectionProperty($this->cut, '_responseParser');
        $rprop->setAccessible(true);
        $rObj->setResponseParser($p);
        $this->assertEquals($p, $rprop->getValue($rObj));
    }
    public function testSetResponseParserEnforcesPrivatePinterface()
    {
        $rObj = new $this->cut();
        $p = new ArrayObject();
        $rprop = new ReflectionProperty($this->cut, '_pinterface');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, 'Countable');
        $rprop = new ReflectionProperty($rObj, '_responseParser');
        $rprop->setAccessible(true);
        $rObj->setResponseParser($p);
        $this->assertEquals($p, $rprop->getValue($rObj));
    }
    /**
     * @expectedException Exception
     */
    public function testSetResponseParserEnforcesPrivatePinterfaceFailTest()
    {
        $rObj = new $this->cut();
        $p = new stdClass();
        $rprop = new ReflectionProperty($this->cut, '_pinterface');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, 'Countable');
        $rprop = new ReflectionProperty($rObj, '_responseParser');
        $rprop->setAccessible(true);
        $rObj->setResponseParser($p);
        $this->assertEquals($p, $rprop->getValue($rObj));
    }
    public function testGetResponseParserReturnsPrivateResponseParser()
    {
        $rObj = new $this->cut();
        $p = new stdClass();
        $rprop = new ReflectionProperty($rObj, '_responseParser');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, $p);
        $rprop = new ReflectionProperty($rObj, '_responseParser');
        $rprop->setAccessible(true);
        $rObj->getResponseParser($p);
        $this->assertEquals($p, $rprop->getValue($rObj));
    }
    public function testGetResponseFormatTypeReturnsPrivateResponseFormat()
    {
        $rObj = new $this->cut();
        $p = 'fooFormat';
        $rprop = new ReflectionProperty($rObj, '_responseFormat');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, $p);
        $rprop = new ReflectionProperty($rObj, '_responseFormat');
        $rprop->setAccessible(true);
        $rObj->getResponseFormatType($p);
        $this->assertEquals($p, $rprop->getValue($rObj));
    }
    public function testGetOutputFormatTypeReturnsPrivateOutputFormat()
    {
        $rObj = new $this->cut();
        $p = 'fooFormat';
        $rprop = new ReflectionProperty($rObj, '_outputFormat');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, $p);
        $rprop = new ReflectionProperty($rObj, '_outputFormat');
        $rprop->setAccessible(true);
        $rObj->getOutputFormatType();
        $this->assertEquals($p, $rprop->getValue($rObj));
    }
    public function testSetOutputFormatTypeStoresPrivateOutputFormat()
    {
        $rObj = new $this->cut();
        $p = 'fooFormat';
        $rprop = new ReflectionProperty($rObj, '_outputFormat');
        $rprop->setAccessible(true);
        $rObj->setOutputFormatType($p);
        $this->assertEquals($p, $rprop->getValue($rObj));
    }
    public function testGetInputFormatTypeReturnsPrivateInputFormat()
    {
        $rObj = new $this->cut();
        $p = 'fooFormat';
        $rprop = new ReflectionProperty($rObj, '_inputFormat');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, $p);
        $rprop = new ReflectionProperty($rObj, '_inputFormat');
        $rprop->setAccessible(true);
        $rObj->getInputFormatType();
        $this->assertEquals($p, $rprop->getValue($rObj));
    }
    public function testSetInputFormatTypeStoresPrivateInputFormat()
    {
        $rObj = new $this->cut();
        $p = 'fooFormat';
        $rprop = new ReflectionProperty($rObj, '_inputFormat');
        $rprop->setAccessible(true);
        $rObj->setInputFormatType($p);
        $this->assertEquals($p, $rprop->getValue($rObj));
    }
    public function getParsedDataArray()
    {
        return array(
            'cpanelresult' => array(
                'data' => 1
            )
        );
    }
    public function getRawJsonData()
    {
        return json_encode($this->getParsedDataArray());
    }
    public function testSetResponseUsesUnderlyingSetOptionsOnPrivateResponse()
    {
        $rObj = new $this->cut();
        $arg = $this->getParsedDataArray();
        $container = $this->getMock('Cpanel_Core_Object', array(
            'setOptions'
        ));
        $container->expects($this->once())->method('setOptions')->with($arg);
        $rprop = new ReflectionProperty($this->cut, '_response');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, $container);
        $rObj->setResponse($arg);
    }
    public function testGetResponseCallsGetOutputFormatTypeByDefaultAndReturnsSelf()
    {
        $rObj = $this->getRObj(array(
            'getOutputFormatType'
        ));
        $rprop = new ReflectionProperty($this->cut, '_defaultOutputFormat');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, 'fooFormat');
        $rObj->expects($this->once())->method('getOutputFormatType')->will($this->returnValue('fooFormat'));
        $actual = $rObj->getResponse();
        $this->assertEquals($rObj, $actual);
    }
    /**
     * @depends testSetResponseUsesUnderlyingSetOptionsOnPrivateResponse
     */
    public function testGetResponseReturnsArrayOnRequest()
    {
        $rObj = new $this->cut();
        $expected = $this->getParsedDataArray();
        $rObj->setResponse($expected);
        $actual = $rObj->getResponse('array');
        $this->assertEquals($expected, $actual);
    }
    public function testGetResponseReturnsRawResponseWhenRequestingSameAsRFT()
    {
        $rObj = $this->getRObj(array(
            'getResponseFormatType',
            'getRawResponse'
        ));
        $expected = 'fooFormatedString';
        $rType = 'foo';
        $rObj->expects($this->once())->method('getResponseFormatType')->will($this->returnValue($rType));
        $rObj->expects($this->once())->method('getRawResponse')->will($this->returnValue($expected));
        $actual = $rObj->getResponse($rType);
        $this->assertEquals($expected, $actual);
    }
    public function testGetResponseWillAttemptToEncodeParsedResponseWithValidParser()
    {
        $rObj = $this->getRObj(array(
            'getValidParser'
        ));
        $p = $this->getParser(true, array(
            'encodeQueryObject'
        ));
        $json = $this->getRawJsonData();
        $p->expects($this->once())->method('encodeQueryObject')->with($rObj)->will($this->returnValue($json));
        $rObj->expects($this->once())->method('getValidParser')->will($this->returnValue($p));
        $this->assertEquals($json, $rObj->getResponse('mocking'));
    }
    public function testSetResponseErrorStoresStringInPrivateResponseErrors()
    {
        $rObj = new $this->cut();
        $rprop = new ReflectionProperty($this->cut, '_responseErrors');
        $rprop->setAccessible(true);
        $foo = 'Some Error String';
        $rmeth = new ReflectionMethod($rObj, 'setResponseError');
        $rmeth->setAccessible(true);
        $rmeth->invoke($rObj, $foo);
        $expected = array(
            $foo
        );
        $actual = $rprop->getValue($rObj);
        $this->assertEquals($expected, $actual);
    }
    /**
     * @depends testSetResponseErrorStoresStringInPrivateResponseErrors
     */
    public function testSetResponseErrorStoresArrayContentsInPrivateResponseErrors()
    {
        $rObj = new $this->cut();
        $rprop = new ReflectionProperty($this->cut, '_responseErrors');
        $rprop->setAccessible(true);
        $foo = 'First Error String';
        $baz[] = 'Some Error String';
        $baz[] = 'More Error String';
        $baz[] = 'Most Error String';
        $rmeth = new ReflectionMethod($rObj, 'setResponseError');
        $rmeth->setAccessible(true);
        $rmeth->invoke($rObj, $foo);
        $rmeth->invoke($rObj, $baz);
        array_unshift($baz, $foo);
        $expected = $baz;
        $actual = $rprop->getValue($rObj);
        $this->assertEquals($expected, $actual);
    }
    public function testValidResponseReturnTrueOnNoStoredErrors()
    {
        $rObj = new $this->cut();
        $condition = $rObj->validResponse();
        $this->assertTrue($condition);
    }
    /**
     * @depends testSetResponseErrorStoresStringInPrivateResponseErrors
     */
    public function testValidResponseReturnFalseOnStoredErrors()
    {
        $rObj = new $this->cut();
        $foo = 'First Error String';
        $rmeth = new ReflectionMethod($rObj, 'setResponseError');
        $rmeth->setAccessible(true);
        $rmeth->invoke($rObj, $foo);
        $condition = $rObj->validResponse();
        $this->assertFalse($condition);
    }
    public function testGetResponseErrorsReturnNullOnNoStoredErrors()
    {
        $rObj = new $this->cut();
        $this->assertNull($rObj->getResponseErrors());
    }
    /**
     * @depends testSetResponseErrorStoresStringInPrivateResponseErrors
     */
    public function testGetResponseErrorsReturnArrayOnStoredErrorsAndDoesNotFlush()
    {
        $rObj = new $this->cut();
        $baz[] = 'Some Error String';
        $baz[] = 'More Error String';
        $baz[] = 'Most Error String';
        $rmeth = new ReflectionMethod($rObj, 'setResponseError');
        $rmeth->setAccessible(true);
        $rmeth->invoke($rObj, $baz);
        $rprop = new ReflectionProperty($this->cut, '_responseErrors');
        $rprop->setAccessible(true);
        $actual = $rObj->getResponseErrors();
        $this->assertEquals($baz, $actual);
        $this->assertEquals($baz, $rprop->getValue($rObj));
    }
    /**
     * @depends testSetResponseErrorStoresStringInPrivateResponseErrors
     */
    public function testGetResponseErrorsReturnArrayOnStoredErrorsAndWillFlushOnRequest()
    {
        $rObj = new $this->cut();
        $baz[] = 'Some Error String';
        $baz[] = 'More Error String';
        $baz[] = 'Most Error String';
        $rmeth = new ReflectionMethod($rObj, 'setResponseError');
        $rmeth->setAccessible(true);
        $rmeth->invoke($rObj, $baz);
        $rprop = new ReflectionProperty($this->cut, '_responseErrors');
        $rprop->setAccessible(true);
        $actual = $rObj->getResponseErrors(true);
        $this->assertEquals($baz, $actual);
        $this->assertEquals(array(), $rprop->getValue($rObj));
    }
    public function testGetRawResponseReturnsPrivateRawResponse()
    {
        $rObj = new $this->cut();
        $p = 'fooFormat';
        $rprop = new ReflectionProperty($rObj, '_rawResponse');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, $p);
        $rprop = new ReflectionProperty($rObj, '_rawResponse');
        $rprop->setAccessible(true);
        $rObj->getOutputFormatType();
        $this->assertEquals($p, $rprop->getValue($rObj));
    }
    public function testSetQueryUsesUnderlyingContainer()
    {
        $rObj = new $this->cut();
        $arg = $this->getParsedDataArray();
        $container = $this->getMock('Cpanel_Core_Object', array(
            'setOptions'
        ));
        $container->expects($this->once())->method('setOptions')->with($arg);
        $rprop = new ReflectionProperty($this->cut, '_query');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, $container);
        $rObj->setQuery($arg);
    }
    public function testGetQueryReturnsContainer()
    {
        $rObj = new $this->cut();
        $arg = $this->getParsedDataArray();
        $container = new Cpanel_Core_Object($arg);
        $rprop = new ReflectionProperty($this->cut, '_query');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, $container);
        $actual = $rObj->getQuery();
        $this->assertEquals($container, $actual);
    }
    public function testSetRawResponseDoesSomething()
    {
        $rObj = new $this->cut();
        $foo = 'raw response string';
        $rprop = new ReflectionProperty($this->cut, '_rawResponse');
        $rprop->setAccessible(true);
        $rObj->setRawResponse($foo);
        $this->assertEquals($foo, $rprop->getValue($rObj));
    }
    public function testSetRawResponseStoresNullByDefault()
    {
        $rObj = new $this->cut();
        $rprop = new ReflectionProperty($this->cut, '_rawResponse');
        $rprop->setAccessible(true);
        $rObj->setRawResponse();
        $this->assertNull($rprop->getValue($rObj));
    }
    public function testSetRawResponseLogsToListnerIfAvaliableAndInDebugLogMode()
    {
        $foo = 'raw response string';
        $expected = "Storing RawResponse:\n$foo";
        $l = $this->getMock('Cpanel_Listner_Subject_Logger', array(
            'log'
        ));
        $l->expects($this->once())->method('log')->with('debug', $expected)->will($this->returnValue($this->anything()));
        $rObj = new $this->cut(array(
            'listner' => $l
        ));
        $rprop = new ReflectionProperty($this->cut, '_rawResponse');
        $rprop->setAccessible(true);
        $rObj->setRawResponse($foo);
        $this->assertEquals($foo, $rprop->getValue($rObj));
    }
    /**
     * @depends testSetRawResponseDoesSomething
     * @depends testSetResponseErrorStoresStringInPrivateResponseErrors
     * @depends testSetResponseUsesUnderlyingSetOptionsOnPrivateResponse
     * @depends testGetResponseReturnsArrayOnRequest
     * @depends testSetOutputFormatTypeStoresPrivateOutputFormat
     * @depends testGetRawResponseReturnsPrivateRawResponse
     * @depends testGetResponseParserReturnsPrivateResponseParser
     */
    public function testProtectedParseWithParserUnderNormalConditions()
    {
        $raw = $this->getRawJsonData();
        $rObj = new $this->cut();
        $rObj->setRawResponse($raw);
        $rObj->setResponseFormatType('JSON');
        $rmeth = new ReflectionMethod($rObj, '_parseWithParser');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($rObj);
        $this->assertEquals($this->getParsedDataArray(), $actual);
    }
    /**
     * @expectedException Exception
     * @depends           testSetRawResponseDoesSomething
     * @depends           testSetResponseErrorStoresStringInPrivateResponseErrors
     * @depends           testSetResponseUsesUnderlyingSetOptionsOnPrivateResponse
     * @depends           testGetResponseReturnsArrayOnRequest
     * @depends           testSetOutputFormatTypeStoresPrivateOutputFormat
     * @depends           testGetRawResponseReturnsPrivateRawResponse
     * @depends           testGetResponseParserReturnsPrivateResponseParser
     */
    public function testProtectedParseThrowsOnParserNotImplementingPrivatePinterface()
    {
        $raw = $this->getRawJsonData();
        $rObj = new $this->cut();
        $rObj->setRawResponse($raw);
        $rObj->setResponseFormatType('JSON');
        $rprop = new ReflectionProperty($rObj, '_responseParser');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, new stdClass());
        $rmeth = new ReflectionMethod($rObj, '_parseWithParser');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($rObj);
    }
    /**
     * @depends testProtectedParseWithParserUnderNormalConditions
     * @depends testProtectedParseThrowsOnParserNotImplementingPrivatePinterface
     */
    public function testParseUnderNormalConditions()
    {
        $raw = $this->getRawJsonData();
        $rObj = new $this->cut();
        $rObj->setResponseFormatType('JSON');
        $rObj->setOutputFormatType('array');
        $actual = $rObj->parse($raw);
        $expected = $this->getParsedDataArray();
        $this->assertEquals($expected, $actual);
    }
    /**
     * @depends testParseUnderNormalConditions
     */
    public function testParseUnderSetsErrorOnParseErrorAndHasArray()
    {
        $raw = $this->getRawJsonData() . '}';
        $rObj = new $this->cut();
        $rObj->setResponseFormatType('JSON');
        $rObj->setOutputFormatType('array');
        $actual = $rObj->parse($raw);
        $expected = array();
        $this->assertEquals($expected, $actual);
        $errstr = implode(',', $rObj->getResponseErrors());
        $this->assertContains($rObj::ERROR_RESPONSE, $errstr);
    }
    /**
     * @depends testParseUnderNormalConditions
     */
    public function testParseDoesNotCallUnderscorePareWithParserWhenADirectURLQuery()
    {
        $raw = $this->getRawJsonData();
        $rObj = $this->getRObj(array(
            'setResponse'
        ));
        $rObj->expects($this->once())->method('setResponse')->with(array());
        $rObj->query->directURL = true;
        $rObj->setResponseFormatType('JSON');
        $rObj->setOutputFormatType('array');
        $actual = $rObj->parse($raw);
        $expected = array();
        $this->assertEquals($expected, $actual);
    }
    public function testMagicGetUsesUnderlyingResponseContainerByDefault()
    {
        $rObj = new $this->cut();
        $arg = $this->getParsedDataArray();
        $container = new Cpanel_Core_Object($arg);
        $rprop = new ReflectionProperty($this->cut, '_response');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, $container);
        $acont = $rObj->cpanelresult;
        $this->assertEquals($arg['cpanelresult']['data'], $acont->data);
    }
    public function testMagicGetReturnsQueryContainerOnRequest()
    {
        $rObj = new $this->cut();
        $arg = $this->getParsedDataArray();
        $container = new Cpanel_Core_Object($arg);
        $rprop = new ReflectionProperty($this->cut, '_query');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, $container);
        $acont = $rObj->query;
        $this->assertEquals($arg['cpanelresult']['data'], $acont->cpanelresult->data);
    }
    public function testMagicSetCallsSetResponse()
    {
        $key = 'foo';
        $value = 'bar';
        $rObj = $this->getRObj(array(
            'setResponse'
        ));
        $rObj->expects($this->once())->method('setResponse')->with(array(
            $key => $value
        ));
        $rObj->$key = $value;
    }
    public function testMagicToStringReturnsPrintROutputOfResponseContainer()
    {
        $rObj = new $this->cut();
        $arg = $this->getParsedDataArray();
        $container = new Cpanel_Core_Object($arg);
        $expected = print_r($container, true);
        $rprop = new ReflectionProperty($this->cut, '_response');
        $rprop->setAccessible(true);
        $rprop->setValue($rObj, $container);
        $actual = (string)$rObj;
        $this->assertEquals($expected, $actual);
    }
}
