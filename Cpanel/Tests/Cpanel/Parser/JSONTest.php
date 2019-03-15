<?php



/**
 * @covers Cpanel_Parser_JSON
 * @author davidneimeyer
 *         
 */
class Cpanel_Parser_JSONTest extends CpanelTestCase
{
    /**
     * @var Cpanel_Parser_JSON
     */
    protected $cut = 'Cpanel_Parser_JSON';
    protected $interface = 'Cpanel_Parser_Interface';
    protected $PARSER_TYPE = 'JSON';
    protected $qa = 'Cpanel_Query_Object';
    /**
     * 
     * @param unknown_type       $methods  
     * @param unknown_type       $args     
     * @param unknown_type       $mockName 
     * @param unknown_type       $callConst
     * @param unknown_type       $callClone
     * @param unknown_type       $callA    
     *                                       
     * @note   This method actually returns a Mock Class
     * 
     * @return Cpanel_Parser_JSON
     */
    public function getP($methods = array(), $args = array(), $mockName = '', $callConst = true, $callClone = true, $callA = true)
    {
        if (empty($methods)) {
            $methods = null;
        }
        $m = $this->_makeMock($this->cut, $methods, $args, $mockName, $callConst, $callClone, $callA);
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
            return $this->_makeMock($this->qa, $methods, $args, $mockName, $callConst, $callClone, $callA);
        }
        return new Cpanel_Query_Object();
    }
    public function testHasConstants()
    {
        $classname = $this->cut;
        $this->assertEquals($this->PARSER_TYPE, $classname::PARSER_TYPE);
        $this->assertTrue(defined("{$classname}::ERROR_DECODE"));
        $this->assertTrue(defined("{$classname}::CONDENSED_MODE"));
        $this->assertTrue(defined("{$classname}::EXPANDED_MODE"));
    }
    public function testConstructCallSetModeByDefaultWithCondensedMode()
    {
        $classname = $this->cut;
        $c = $classname::CONDENSED_MODE;
        $p = $this->getP(array(
            'setMode'
        ), array(), '', false);
        $p->expects($this->exactly(1))->method('setMode')->with($c);
        $p->__construct();
    }
    public function constructInput()
    {
        $classname = $this->cut;
        $e = $classname::EXPANDED_MODE;
        $c = $classname::CONDENSED_MODE;
        $cpObj = new Cpanel_Core_Object(array(
            'mode' => $e
        ));
        return array(
            array(
                array(
                    'mode' => $e
                ),
                1,
                $e
            ),
            array(
                array(
                    'mode' => $c
                ),
                1,
                $c
            ),
            array(
                array(),
                0,
                $c
            ),
            array(
                '',
                0,
                $c
            ),
            array(
                $cpObj,
                1,
                $e
            ),
        );
    }
    /**
     * 
     * @dataProvider constructInput
     *               
     * @depends      testConstructCallSetModeByDefaultWithCondensedMode
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
    public function setModeInput()
    {
        $classname = $this->cut;
        $e = $classname::EXPANDED_MODE;
        $c = $classname::CONDENSED_MODE;
        $cpObj = new Cpanel_Core_Object(array(
            'mode' => $e
        ));
        return array(
            array(
                $e,
                $e
            ),
            array(
                $c,
                $c
            ),
            array(
                '',
                $c
            ),
            array(
                'blah',
                $c
            ),
            array(
                array(),
                $c
            ),
            array(
                $cpObj,
                $c
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
                true
            ),
            array(
                'json',
                true
            ),
            array(
                'XML',
                false
            ),
            array(
                'xml',
                false
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
    public function getParsedDataArray()
    {
        return array(
            'cpanelresult' => array(
                'data' => 1
            )
        );
    }
    //large data is for verifying all the features of prettyPrint
    public function getParsedDataLargeArray()
    {
        return array(
            "cpanelresult" => array(
                'data' => array(
                    0 => 'zero',
                    1 => 'one'
                ),
                'more' => 'foo'
            )
        );
    }
    public function getRawJsonData()
    {
        return json_encode($this->getParsedDataArray());
    }
    //large data is for verifying all the features of prettyPrint
    public function getRawJsonDataLarge()
    {
        $json = json_encode($this->getParsedDataLargeArray());
        $json = str_replace(":", ":\n", $json);
        return $json;
    }
    public function getPrettyJSONData($int = "\t")
    {
        return "{\n{$int}\"cpanelresult\":{\n{$int}{$int}\"data\":1\n{$int}}\n}";
    }
    // large data is for verifying all the features of prettyPrint
    public function getPrettyJSONDataLarge($int = "\t")
    {
        return "{\n{$int}\"cpanelresult\":\n{$int}{$int}{\n{$int}{$int}\"data\":\n{$int}{$int}[\n{$int}{$int}{$int}" . "\"zero\",\n{$int}{$int}{$int}\"one\"\n{$int}{$int}],\n{$int}{$int}\"more\":\n{$int}{$int}\"foo\"\n{$int}}\n}";
    }
    public function getJSONErrorMsg($errCode)
    {
        switch ($errCode) {
            case JSON_ERROR_DEPTH:
                return 'The maximum stack depth has been exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Invalid or malformed JSON';
            case JSON_ERROR_CTRL_CHAR:
                return 'Control character error, possibly incorrectly encoded';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            case JSON_ERROR_RECURSION:
                return 'One or more recursive references in the value to be encoded';
            case JSON_ERROR_INF_OR_NAN:
                return 'One or more NAN or INF values in the value to be encoded';
            case JSON_ERROR_UNSUPPORTED_TYPE:
                return 'A value of a type that cannot be encoded was given';
            case JSON_ERROR_INVALID_PROPERTY_NAME:
                return 'A property name that cannot be encoded was given';
            case JSON_ERROR_UTF16:
                return 'Malformed UTF-16 characters, possibly incorrectly encoded';
            case JSON_ERROR_NONE:
            default:
                return '';
        }
    }
    /**
     * @dataProvider rftData
     */
    public function testCanParseOnlyReturnsTrueOnJSON($type, $expected)
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
        $customDefault = 'JSON_did_not_error_but_something_is_wrong_in_the_parsing_of_the_response';
        $r = $p->getParserInternalErrors($prefix, $customDefault);
        $condition1 = (strpos($r, $customDefault) !== false);
        $condition2 = (strpos($r, $hardCodedDefault) !== false);
        $this->assertTrue($condition1);
        $this->assertFalse($condition2);
    }
    public function badJSONData()
    {
        $raw = $this->getRawJsonData();
        return array(
            array(
                chr(27) . $raw,
                JSON_ERROR_CTRL_CHAR
            ),
            array(
                "{ 'bad':" . $raw . "}",
                JSON_ERROR_SYNTAX
            ),
            array(
                $raw . "}",
                JSON_ERROR_SYNTAX
            ),
        );
    }
    /**
     * @dataProvider badJSONData
     */
    public function testGetParserInternalErrorsReturnExactJsonFunctionMessageWhenJsonUnderscoreDecodeFails($json, $errCode)
    {
        $p = new $this->cut();
        $prefix = '';
        $rprop = new ReflectionProperty($p, '_hasParseError');
        $rprop->setAccessible(true);
        $rprop->setValue($p, true);
        json_decode($json);
        $rmeth = new ReflectionMethod($p, 'getParserInternalErrors');
        $rparams = $rmeth->getParameters();
        $hardCodedDefault = $rparams[1]->getDefaultValue();
        $customDefault = 'JSON_did_not_error_but_something_is_wrong_in_the_parsing_of_the_response';
        $r = $p->getParserInternalErrors($prefix, $customDefault);
        $expected = $this->getJSONErrorMsg($errCode);
        $this->assertEquals($expected, $r);
        //flush the error buffer
        json_decode($this->getRawJsonData());
    }
    public function parseMethodData()
    {
        return array(
            array(
                $this->getRawJsonData(),
                0
            ),
            array(
                array(),
                1
            ),
            array(
                new stdClass(),
                1
            ),
            array(
                '',
                0
            ),
            array(
                1,
                1
            ),
            array(
                0,
                1
            ),
            array(
                true,
                1
            ),
            array(
                false,
                1
            ),
        );
    }
    /**
     * @depends      testGetParserInternalErrorsReturnExactJsonFunctionMessageWhenJsonUnderscoreDecodeFails
     * @dataProvider parseMethodData
     *               
     * @paramsunknown_type $input  
     * @paramsunknown_type $expectE
     */
    public function testParseThrowOnBadInput($input, $expectE)
    {
        if ($expectE) {
            $this->expectException('Exception');
        }
        $p = new $this->cut();
        $result = $p->parse($input);
        if(!$expectE && $input == ''){
            $this->assertEquals($result, 'JSON Decode - Cannot decode empty string.');
        }else{
            $this->assertEquals($result, $this->getParsedDataArray());
        }
    }
    public function parseMethodData2()
    {
        $classname = $this->cut;
        $classerr = $classname::ERROR_DECODE;
        $raw = $this->getRawJsonData();
        return array(
            array(
                $this->getRawJsonData(),
                $this->getParsedDataArray()
            ),
            array(
                '',
                $classerr . 'Cannot decode empty string.'
            ),
            array(
                chr(27) . $raw,
                $classerr . $this->getJSONErrorMsg(JSON_ERROR_CTRL_CHAR)
            ),
            array(
                "{ 'bad':" . $raw . "}",
                $classerr . $this->getJSONErrorMsg(JSON_ERROR_SYNTAX)
            ),
            array(
                $raw . "}",
                $classerr . $this->getJSONErrorMsg(JSON_ERROR_SYNTAX)
            ),
        );
    }
    /**
     * @depends      testParseThrowOnBadInput
     * @dataProvider parseMethodData2
     *               
     * @paramsunknown_type $input  
     * @paramsunknown_type $expectE
     */
    public function testParseSetsPrivateHasParseError($input, $msg)
    {
        $p = new $this->cut();
        $rprop = new ReflectionProperty($p, '_hasParseError');
        $rprop->setAccessible(true);
        $p->parse($input);
        $actual = $rprop->getValue($p);
        if (is_string($msg)) {
            $expected = true;
        } else {
            $expected = false;
        }
        $this->assertEquals($expected, $actual);
    }
    /**
     * @depends      testParseSetsPrivateHasParseError
     * @dataProvider parseMethodData2
     *               
     * @paramsunknown_type $input  
     * @paramsunknown_type $expectE
     */
    public function testParseReturnStringOrArray($input, $msg)
    {
        $p = new $this->cut();
        $actual = $p->parse($input);
        $this->assertEquals($msg, $actual);
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
                $this->getParsedDataArray(),
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
            $this->expectException('Exception');
        }
        $result = $p->encodeQueryObject($input);
        if(!$expectE){
            $this->assertEquals($result, '[]');
        }
    }
    public function testEncodeQueryObjectThrowOnBadEncode()
    {
        $this->expectException('Exception');
        $p = new $this->cut();
        $data = array(
            'foo' => 'bar',
            'baz' => curl_init()
        );
        $rObj = $this->getRObj();
        $rObj->setResponse($data);
        $r = $p->encodeQueryObject($rObj);
    }
    public function testEncodeQueryObjectCallsGetResponseAndEncodes()
    {
        $p = new $this->cut();
        $rObj = $this->getRObj(true, array(
            'getResponse'
        ));
        $rObj->expects($this->once())->method('getResponse')->with('array')->will($this->returnValue($this->getParsedDataArray()));
        $expected = $this->getRawJsonData();
        $actual = $p->encodeQueryObject($rObj);
        $this->assertEquals($expected, $actual);
    }
    public function testEncodeQueryObjectReturnsPrettyAsNecessary()
    {
        $p = new $this->cut();
        $p->setMode($p::EXPANDED_MODE);
        $rObj = $this->getRObj(true, array(
            'getResponse'
        ));
        $rObj->expects($this->once())->method('getResponse')->with('array')->will($this->returnValue($this->getParsedDataArray()));
        $expected = $this->getPrettyJSONData();
        $actual = $p->encodeQueryObject($rObj);
        $this->assertEquals($expected, $actual);
    }
    public function testPrettyAsSpecified()
    {
        $int = "\t\t\t";
        $p = new $this->cut();
        $expected = $this->getPrettyJSONData($int);
        $actual = $p::prettyPrint($this->getRawJsonData(), array(
            'indent' => $int
        ));
        $this->assertEquals($expected, $actual);
    }
    public function testPrettyLargeData()
    {
        $int = "";
        $p = new $this->cut();
        $expected = $this->getPrettyJSONDataLarge($int);
        $actual = $p::prettyPrint($this->getRawJsonDataLarge($int), array(
            'indent' => $int
        ));
        $this->assertEquals($expected, $actual);
    }
}
