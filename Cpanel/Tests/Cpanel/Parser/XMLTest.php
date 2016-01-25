<?php
/**
 * @covers Cpanel_Parser_XML
 * @author davidneimeyer
 *         
 */
class Cpanel_Parser_XMLTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Cpanel_Parser_XML
     */
    protected $cut = 'Cpanel_Parser_XML';
    protected $interface = 'Cpanel_Parser_Interface';
    protected $PARSER_TYPE = 'XML';
    protected $qa = 'Cpanel_Query_Object';
    /**
     * 
     * @param unknown_type      $methods  
     * @param unknown_type      $args     
     * @param unknown_type      $mockName 
     * @param unknown_type      $callConst
     * @param unknown_type      $callClone
     * @param unknown_type      $callA    
     *                                      
     * @note   This method actually returns a Mock Class
     * 
     * @return Cpanel_Parser_XML
     */
    public function getP($methods = array(), $args = array(), $mockName = '', $callConst = true, $callClone = true, $callA = true)
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
    public function testHasConstants()
    {
        $classname = $this->cut;
        $this->assertEquals($this->PARSER_TYPE, $classname::PARSER_TYPE);
        $this->assertTrue(defined("{$classname}::ERROR_DOM"));
        $this->assertTrue(defined("{$classname}::ERROR_SIMPLEXML"));
        $this->assertTrue(defined("{$classname}::DOM_MODE"));
        $this->assertTrue(defined("{$classname}::DOM_MODE_EXTENDED"));
        $this->assertTrue(defined("{$classname}::SIMPLEXML_MODE"));
    }
    public function testConstructCallSetModeByDefaultWithDomMode()
    {
        $classname = $this->cut;
        $c = $classname::DOM_MODE;
        $p = $this->getP(array(
            'setMode'
        ), array(), '', false);
        $p->expects($this->exactly(1))->method('setMode')->with($c);
        $p->__construct();
    }
    public function constructInput()
    {
        $classname = $this->cut;
        $d = $classname::DOM_MODE;
        $de = $classname::DOM_MODE_EXTENDED;
        $s = $classname::SIMPLEXML_MODE;
        $cpObj = new Cpanel_Core_Object(array(
            'mode' => $de
        ));
        return array(
            array(
                array(
                    'mode' => $de
                ),
                1,
                $de
            ),
            array(
                array(
                    'mode' => $d
                ),
                1,
                $d
            ),
            array(
                array(
                    'mode' => $s
                ),
                1,
                $s
            ),
            array(
                array(),
                0,
                $d
            ),
            array(
                '',
                0,
                $d
            ),
            array(
                $cpObj,
                1,
                $de
            ),
        );
    }
    /**
     * 
     * @dataProvider constructInput
     *               
     * @depends      testConstructCallSetModeByDefaultWithDomMode
     * @paramsunknown_type $input
     * @paramsunknown_type $mode 
     */
    public function testConstructSetsModeViaConfig($input, $index, $mode)
    {
        $p = $this->getP(array(
            'setMode'
        ), array(), '', false);
        $p->expects($this->at($index))->method('setMode')->with($mode);
        $p->__construct($input);
    }
    public function testConstructWillCheckLibXMLSuppression()
    {
        $p = $this->getP(array(
            'getOption'
        ), array(), '', false);
        $p->expects($this->at(0))->method('getOption')->with('disableSuppressLibXML')->will($this->returnValue(true));
        $p->__construct();
    }
    public function setModeInput()
    {
        $classname = $this->cut;
        $d = $classname::DOM_MODE;
        $de = $classname::DOM_MODE_EXTENDED;
        $s = $classname::SIMPLEXML_MODE;
        $cpObj = new Cpanel_Core_Object(array(
            'mode' => $de
        ));
        return array(
            array(
                $de,
                $de
            ),
            array(
                $d,
                $d
            ),
            array(
                $s,
                $s
            ),
            array(
                '',
                $d
            ),
            array(
                'blah',
                $d
            ),
            array(
                array(),
                $d
            ),
            array(
                $cpObj,
                $d
            ),
        );
    }
    /**
     * @depends      testHasConstants
     * @dataProvider setModeInput
     */
    public function testSetMode($input, $mode)
    {
        $p = new $this->cut();
        $p->mode = '';
        $p->setMode($input);
        $this->assertEquals($p->mode, $mode);
    }
    public function testPrivateHasParseError()
    {
        $p = new $this->cut();
        $this->assertAttributeEquals(null, '_hasParseError', $p);
    }
    public function testInterface()
    {
        $p = new $this->cut();
        $this->assertInstanceOf($this->interface, $p);
    }
    public function rftData()
    {
        return array(
            array(
                'JSON',
                false
            ),
            array(
                'json',
                false
            ),
            array(
                'XML',
                true
            ),
            array(
                'xml',
                true
            ),
            array(
                'LiveJSON',
                false
            ),
            array(
                'livejson',
                false
            ),
            array(
                '',
                false
            ),
            array(
                '1',
                false
            ),
            array(
                '0',
                false
            ),
        );
    }
    /**
     * @dataProvider rftData
     */
    public function testCanParseOnlyReturnsTrueOnXML($type, $expected)
    {
        $p = new $this->cut();
        $this->assertEquals($expected, $p->canParse($type));
    }
    public function testGetParserInternalErrorsParameters()
    {
        $p = new $this->cut();
        $rmeth = new ReflectionMethod($p, 'getParserInternalErrors');
        $rparams = $rmeth->getParameters();
        $expected = array(
            'prefix',
            'default'
        );
        foreach ($rparams as $param) {
            $actual[$param->getPosition() ] = $param->getName();
            $this->assertTrue($param->isDefaultValueAvailable());
        }
        $this->assertEquals($expected, $actual);
    }
    /**
     * @depends testPrivateHasParseError
     */
    public function testGetParserInternalErrorsReturnBlankByDefault()
    {
        $p = $this->getP();
        $this->assertEmpty($p->getParserInternalErrors());
    }
    /**
     * @depends testGetParserInternalErrorsReturnBlankByDefault
     */
    public function testGetParserInternalErrorsReturnMinimumOfPrefix()
    {
        $p = new $this->cut();
        $prefix = 'foo';
        $rprop = new ReflectionProperty($p, '_hasParseError');
        $rprop->setAccessible(true);
        $rprop->setValue($p, true);
        $r = $p->getParserInternalErrors($prefix);
        $condition = (strpos($r, $prefix) === 0);
        $this->assertTrue($condition);
    }
    /**
     * @depends testGetParserInternalErrorsParameters
     */
    public function testGetParserInternalErrorsReturnGenericMessageWhenPrivateHasParseErrorIsSet()
    {
        $p = new $this->cut();
        $prefix = 'foo';
        $rprop = new ReflectionProperty($p, '_hasParseError');
        $rprop->setAccessible(true);
        $rprop->setValue($p, true);
        $rmeth = new ReflectionMethod($p, 'getParserInternalErrors');
        $rparams = $rmeth->getParameters();
        $hardCodedDefault = $rparams[1]->getDefaultValue();
        $r = $p->getParserInternalErrors($prefix);
        $condition = (strpos($r, $hardCodedDefault) !== false);
        $this->assertTrue($condition);
    }
    /**
     * @depends testGetParserInternalErrorsReturnGenericMessageWhenPrivateHasParseErrorIsSet
     */
    public function testGetParserInternalErrorsReturnUserProvidedGenericMessageWhenPrivateHasParseErrorIsSet()
    {
        $p = new $this->cut();
        $prefix = 'foo';
        $rprop = new ReflectionProperty($p, '_hasParseError');
        $rprop->setAccessible(true);
        $rprop->setValue($p, true);
        $rmeth = new ReflectionMethod($p, 'getParserInternalErrors');
        $rparams = $rmeth->getParameters();
        $hardCodedDefault = $rparams[1]->getDefaultValue();
        $customDefault = 'XML_did_not_error_but_something_is_wrong_in_the_parsing_of_the_response';
        $r = $p->getParserInternalErrors($prefix, $customDefault);
        $condition1 = (strpos($r, $customDefault) !== false);
        $condition2 = (strpos($r, $hardCodedDefault) !== false);
        $this->assertTrue($condition1);
        $this->assertFalse($condition2);
    }
    public function testStrToDOM()
    {
        $xmlstr = $this->getXMLListPopsWithDisk();
        $p = new $this->cut();
        $rmeth = new ReflectionMethod($p, 'strToDOM');
        $rmeth->setAccessible(true);
        $rprop = new ReflectionProperty($p, '_hasParseError');
        $rprop->setAccessible(true);
        $dom = $rmeth->invoke($p, $xmlstr);
        $inErr = $rprop->getValue($p);
        $this->assertEmpty($inErr);
        $this->assertInstanceOf('DOMDocument', $dom);
    }
    /**
     * @dataProvider getBadXML
     */
    public function testStrToDOMwillSetPrivateHasParseError($xmlstr)
    {
        $p = new $this->cut();
        $rmeth = new ReflectionMethod($p, 'strToDOM');
        $rmeth->setAccessible(true);
        $rprop = new ReflectionProperty($p, '_hasParseError');
        $rprop->setAccessible(true);
        $dom = $rmeth->invoke($p, $xmlstr);
        $inErr = $rprop->getValue($p);
        $this->assertTrue($inErr);
        $this->assertFalse($dom);
    }
    /**
     * @depends         testStrToDOM
     * @outputBuffering disabled
     */
    public function testDOMtoArray()
    {
        $xmlstr = $this->getXMLListPopsWithDisk();
        $p = new $this->cut();
        $rmeth = new ReflectionMethod($p, 'strToDOM');
        $rmeth->setAccessible(true);
        $dom = $rmeth->invoke($p, $xmlstr);
        $this->assertInstanceOf('DOMDocument', $dom);
        $rmeth = new ReflectionMethod($p, 'DOMtoArray');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($p, $dom);
        $expected = $this->getArrayListPopsWithDisk();
        $this->assertEquals($expected, $actual);
    }
    public function testStrToSimpleXML()
    {
        $xmlstr = $this->getXMLListPopsWithDisk();
        $p = new $this->cut();
        $rmeth = new ReflectionMethod($p, 'strToSimpleXML');
        $rmeth->setAccessible(true);
        $rprop = new ReflectionProperty($p, '_hasParseError');
        $rprop->setAccessible(true);
        $sxml = $rmeth->invoke($p, $xmlstr);
        $inErr = $rprop->getValue($p);
        $this->assertEmpty($inErr);
        $this->assertInstanceOf('SimpleXMLElement', $sxml);
    }
    /**
     * @dataProvider getBadXML
     */
    public function testStrToSimpleXMLwillSetPrivateHasParseError($xmlstr)
    {
        $p = new $this->cut();
        $rmeth = new ReflectionMethod($p, 'strToSimpleXML');
        $rmeth->setAccessible(true);
        $rprop = new ReflectionProperty($p, '_hasParseError');
        $rprop->setAccessible(true);
        $dom = $rmeth->invoke($p, $xmlstr);
        $inErr = $rprop->getValue($p);
        $this->assertTrue($inErr);
        $this->assertFalse($dom);
    }
    /**
     * @depends testStrToSimpleXML
     */
    public function testSimpleXMLToArray()
    {
        $xmlstr = $this->getXMLListPopsWithDisk();
        $p = new $this->cut();
        $rmeth = new ReflectionMethod($p, 'strToSimpleXML');
        $rmeth->setAccessible(true);
        $sxml = $rmeth->invoke($p, $xmlstr);
        $this->assertInstanceOf('SimpleXMLElement', $sxml);
        $rmeth = new ReflectionMethod($p, 'simpleXMLToArray');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($p, $sxml);
        $r = $this->getArrayListPopsWithDisk();
        $expected = $r['cpanelresult'];
        //        var_dump($actual);
        $this->assertEquals($expected, $actual);
    }
    public function badInputEncode()
    {
        return array(
            array(
                1,
                1
            ),
            array(
                '',
                1
            ),
            array(
                $this->getArrayListPopsWithDisk(),
                1
            ),
            array(
                new stdClass(),
                1
            ),
            array(
                $this->getRObj(),
                0
            ),
        );
    }
    /**
     * @dataProvider badInputEncode()
     */
    public function testEncodeQueryObjectThrowOnBadInput($input, $expectE)
    {
        $p = new $this->cut();
        if ($expectE) {
            $this->setExpectedException('Exception');
        }
        $p->encodeQueryObject($input);
    }
    /**
     * @expectedException Exception
     */
    public function testEncodeQueryObjectThrowOnBadEncode()
    {
        $p = new $this->cut();
        $data = array(
            'foo' => 'bar',
            'baz' => curl_init()
        );
        $rObj = $this->getRObj();
        $rObj->setResponse($data);

        $r = $p->encodeQueryObject(serialize($rObj));
    }
    public function testEncodeQueryObjectCallsGetResponseAndEncodes()
    {
        $p = new $this->cut();
        $rObj = $this->getRObj(true, array(
            'getResponse'
        ));
        $rObj->expects($this->once())->method('getResponse')->with('array')->will($this->returnValue($this->getArrayListPopsWithDisk()));
        $expected = $this->getXMLListPopsWithDisk();

        $actual = $p->encodeQueryObject($rObj);

        $this->assertEquals($expected, $actual);
    }
    public function testEncodeQueryObjectCallsGetResponseAndEncodesSmall()
    {
        $p = new $this->cut();
        $rObj = $this->getRObj(true, array(
            'getResponse'
        ));
        $rObj->expects($this->once())->method('getResponse')->with('array')->will($this->returnValue($this->getArraySmall()));
        $expected = $this->getXMLSmall();
        $actual = $p->encodeQueryObject($rObj);
        $this->assertEquals($expected, $actual);
    }
    public function testEncodeQueryObjectCallsGetResponseAndEncodesTiny()
    {
        $p = new $this->cut();
        $rObj = $this->getRObj(true, array(
            'getResponse'
        ));
        $rObj->expects($this->once())->method('getResponse')->with('array')->will($this->returnValue(array()));
        $expected = "<?xml version=\"1.0\"?>\n";
        $actual = $p->encodeQueryObject($rObj);
        $this->assertEquals($expected, $actual);
    }
    public function parseMethodDataDOM()
    {
        $classname = $this->cut;
        $classerr = $classname::ERROR_DOM;
        return array(
            array(
                $this->getXMLListPopsWithDisk(),
                $this->getArrayListPopsWithDisk()
            ),
            array(
                "<root><node>text</node>",
                $classerr . 'Premature end of data in tag root line 1'
            ),
        );
    }
    public function badInput()
    {
        return array(
            array(
                1
            ),
            array(
                0
            ),
            array(
                array()
            ),
            array(
                new stdClass()
            ),
            array(
                $this->getRObj()
            ),
        );
    }
    /**
     * @expectedException Exception
     * @dataProvider      badInput
     * @paramsunknown_type $input
     */
    public function testParseThrowOnBadInput($input)
    {
        $p = new $this->cut();
        $actual = $p->parse($input);
    }
    /**
     * @depends testDOMtoArray
     * @depends      testSimpleXMLToArray
     * @dataProvider parseMethodDataDOM
     *               
     * @paramsunknown_type $input  
     * @paramsunknown_type $expectE
     */
    public function testParseReturnStringOrArrayDOM($input, $msg)
    {
        $p = new $this->cut();
        $actual = $p->parse($input);
        if (is_array($msg)) {
            $msg = array_shift($msg);
        }
        $this->assertEquals($msg, $actual);
    }
    /**
     * @depends testDOMtoArray
     * @depends      testSetMode
     * @dataProvider parseMethodDataDOM
     *               
     * @paramsunknown_type $input  
     * @paramsunknown_type $expectE
     */
    public function testParseReturnStringOrArrayDOME($input, $msg)
    {
        $p = new $this->cut();
        $p->setMode($p::DOM_MODE_EXTENDED);
        $actual = $p->parse($input);
        $this->assertEquals($msg, $actual);
    }
    public function parseMethodDataSXML()
    {
        $classname = $this->cut;
        $classerr = $classname::ERROR_SIMPLEXML;
        return array(
            array(
                $this->getXMLListPopsWithDisk(),
                $this->getArrayListPopsWithDisk()
            ),
            array(
                "<root><node>text</node>",
                $classerr . 'Premature end of data in tag root line 1'
            ),
        );
    }
    /**
     * @depends testSetMode
     * @depends      testSimpleXMLToArray
     * @dataProvider parseMethodDataSXML
     *               
     * @paramsunknown_type $input  
     * @paramsunknown_type $expectE
     */
    public function testParseReturnStringOrArraySXML($input, $msg)
    {
        $p = new $this->cut();
        $p->setMode($p::SIMPLEXML_MODE);
        $actual = $p->parse($input);
        if (is_array($msg)) {
            $msg = array_shift($msg);
        }
        $this->assertEquals($msg, $actual);
    }
    public function getBadXML()
    {
        return array(
            array(
                "<?xml version=\"1.0\" ?><?xml version=\"1.0\" ?><root><node>text</node></root>"
            ),
            array(
                "<root><node>text</node>"
            ),
            array(
                "<?xml version=\"1.0\" ?><root><node>text</root>"
            ),
        );
    }
    public function getArraySmall()
    {
        return array(
            'cpanelresult' => 'foo'
        );
    }
    public function getXMLSmall()
    {
        return "<?xml version=\"1.0\"?>\n<cpanelresult>foo</cpanelresult>\n";
    }
    public function getArrayListPopsWithDisk()
    {
        return array(
            'cpanelresult' => array(
                'apiversion' => '2',
                'data' => array(
                    array(
                        '_diskquota' => '262144000',
                        '_diskused' => '',
                        'diskquota' => '250',
                        'txtdiskquota' => '250',
                        'user' => 'auththis',
                    ),
                    array(
                        '_diskquota' => '262144000',
                        '_diskused' => '0',
                    ),
                    array(
                        '_diskquota' => array(
                            '262144000',
                            '262144001'
                        ),
                        'user' => 'what',
                    ),
                ),
                'event' => array(
                    'result' => '1'
                ),
                'func' => 'listpopswithdisk',
                'module' => 'Email',
            ),
        );
    }
    public function getXMLListPopsWithDisk()
    {
        return "<?xml version=\"1.0\"?>
<cpanelresult>
  <apiversion>2</apiversion>
  <data>
    <_diskquota>262144000</_diskquota>
    <_diskused/>
    <diskquota>250</diskquota>
    <txtdiskquota>250</txtdiskquota>
    <user>auththis</user>
  </data>
  <data>
    <_diskquota>262144000</_diskquota>
    <_diskused>0</_diskused>
  </data>
  <data>
    <_diskquota>262144000</_diskquota>
    <_diskquota>262144001</_diskquota>
    <user>what</user>
  </data>
  <event>
    <result>1</result>
  </event>
  <func>listpopswithdisk</func>
  <module>Email</module>
</cpanelresult>
";
    }
}
