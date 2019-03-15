<?php



/**
 * @covers Cpanel_Parser_LiveJSON
 * @author davidneimeyer
 *         
 */
class Cpanel_Parser_LiveJSONTest extends CpanelTestCase
{
    /**
     * @var Cpanel_Parser_LiveJSON
     */
    protected $cut = 'Cpanel_Parser_LiveJSON';
    protected $interface = 'Cpanel_Parser_Interface';
    protected $PARSER_TYPE = 'LiveJSON';
    protected $qa = 'Cpanel_Query_Object';
    /**
     * 
     * @param unknown_type           $methods  
     * @param unknown_type           $args     
     * @param unknown_type           $mockName 
     * @param unknown_type           $callConst
     * @param unknown_type           $callClone
     * @param unknown_type           $callA    
     *                                           
     * @note   This method actually returns a Mock Class
     * 
     * @return Cpanel_Parser_LiveJSON
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
        return new Cpanel_Query_Object($args);
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
                false
            ),
            array(
                'json',
                false
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
                true
            ),
            array(
                'livejson',
                true
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
        $json = json_encode($this->getParsedDataArray());
        return $json;
    }
    public function getRawLJsonData()
    {
        $json = json_encode($this->getParsedDataArray());
        $json = "<cpanelresult>{$json}</cpanelresult>";
        return $json;
    }
    public function addLiveWrapTag($data = '')
    {
        return "<cpanelresult>{$data}</cpanelresult>";
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
            $errmsg = 'Maximum stack depth exceeded.';
            break;

        case JSON_ERROR_CTRL_CHAR:
            $errmsg = 'Unexpected control character found.';
            break;

        case JSON_ERROR_SYNTAX:
            $errmsg = 'Syntax error, malformed JSON.';
            break;

        case JSON_ERROR_STATE_MISMATCH:
            $errmsg = 'Invalid or malformed JSON.';
            break;

        default:
            $errmsg = '';
        }
        return $errmsg;
    }
    /**
     * @dataProvider rftData
     */
    public function testCanParseOnlyReturnsTrueOnLiveJSON($type, $expected)
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
                $this->getRawLJsonData(),
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
        $munged = str_replace('{', '{' . chr(27), $raw);
        return array(
            array(
                $this->getRawLJsonData(),
                $this->getParsedDataArray()
            ),
            array(
                '',
                $classerr . 'Cannot decode empty string.'
            ),
            array(
                $this->addLiveWrapTag(''),
                $classerr . 'Invalid server response string for LiveJSON parser.'
            ),
            array(
                $this->addLiveWrapTag($munged),
                $classerr . $this->getJSONErrorMsg(JSON_ERROR_CTRL_CHAR)
            ),
            array(
                $this->addLiveWrapTag("{ 'bad':" . $raw . "}"),
                $classerr . $this->getJSONErrorMsg(JSON_ERROR_SYNTAX)
            ),
            array(
                $this->addLiveWrapTag($raw . "}"),
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
    /**
     * @depends testParseReturnStringOrArray
     */
    public function testParserEnsureCpanelResultNest()
    {
        $input = $this->getRawLJsonData();
        $input = str_replace('{"cpanelresult', '{"foo', $input);
        $output = $this->getParsedDataArray();
        $output = $output['cpanelresult'];
        $output = array(
            'cpanelresult' => array(
                'foo' => $output
            )
        );
        $p = new $this->cut();
        $actual = $p->parse($input);
        $this->assertEquals($output, $actual);
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
        // no assertions, not no exceptions either
        $this->expectNotToPerformAssertions();
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
        $ljson = "<cpanelresult>{$expected}</cpanelresult>";
        $this->assertEquals($ljson, $actual);
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
        $ljson = "<cpanelresult>{$expected}</cpanelresult>";
        $this->assertEquals($ljson, $actual);
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
    /**
     * @dataProvider badInputEncode()
     */
    public function testEncodeQueryThrowOnBadInput($input, $expectE)
    {
        $p = new $this->cut();
        if ($expectE) {
            $this->expectException('Exception');
        }
        $p->encodeQuery($input);
        // no assertions, not no exceptions either
        $this->expectNotToPerformAssertions();
    }
    public function rObjData()
    {
        $cpObj = new Cpanel_Core_Object(array(
            'foo' => 'bar'
        ));
        $obj = new stdClass();
        $obj->foo = 'bar';
        $data = array(
            array(
                'reqtype' => 'exec',
                'module' => 'Email',
                'func' => 'listpopswithdisk',
                'apiversion' => '2',
                'args' => '',
                0
            ),
            array(
                'reqtype' => 'exec',
                'module' => 'Email',
                'func' => 'listpopswithdisk',
                'apiversion' => '2',
                'args' => 'string',
                0
            ),
            array(
                'reqtype' => 'exec',
                'module' => 'Email',
                'func' => 'listpopswithdisk',
                'apiversion' => '2',
                'args' => array(
                    'foo' => 'bar'
                ),
                0
            ),
            array(
                'reqtype' => 'exec',
                'module' => 'Email',
                'func' => 'listpopswithdisk',
                'apiversion' => '2',
                'args' => $cpObj,
                0
            ),
            array(
                'reqtype' => 'exec',
                'module' => 'Email',
                'func' => 'listpopswithdisk',
                'apiversion' => '2',
                'args' => $obj,
                0
            ),
            array(
                'reqtype' => 'exec',
                'module' => 'Email',
                'func' => 'listpopswithdisk',
                'apiversion' => '2',
                'args' => curl_init(),
                1
            ),
        );
        return $data;
    }
    /**
     * @dataProvider rObjData
     *               
     * @paramsunknown_type $reqtype  
     * @paramsunknown_type $module   
     * @paramsunknown_type $func     
     * @paramsunknown_type $apiverion
     * @paramsunknown_type $args     
     * @paramsunknown_type $expectE  
     */
    public function testEncodeQueryAcceptsGoodQueryData($reqtype, $module, $func, $apiverion, $args, $expectE)
    {
        $rObj = $this->getRObj();
        $rObj->query->reqtype = $reqtype;
        $rObj->query->module = $module;
        $rObj->query->func = $func;
        $rObj->query->apiversion = $apiverion;
        $rObj->query->args = $args;
        $p = new $this->cut();
        $result = $p->encodeQuery($rObj);
        // no assertions, not no exceptions either
        $this->expectNotToPerformAssertions();
    }
    public function rObjData2()
    {
        $cpObj = new Cpanel_Core_Object(array(
            'foo' => 'bar'
        ));
        $obj = new stdClass();
        $obj->foo = 'bar';
        $data = array(
            array(
                'reqtype' => 'exec',
                'module' => 'Email',
                'func' => 'listpopswithdisk',
                'apiversion' => '2',
                'args' => ''
            ),
            array(
                'reqtype' => 'exec',
                'module' => 'Email',
                'func' => 'listpopswithdisk',
                'apiversion' => '2',
                'args' => 'string'
            ),
            array(
                'reqtype' => 'exec',
                'module' => 'Email',
                'func' => 'listpopswithdisk',
                'apiversion' => '2',
                'args' => array(
                    'foo' => 'bar'
                )
            ),
            array(
                'reqtype' => 'exec',
                'module' => 'Email',
                'func' => 'listpopswithdisk',
                'apiversion' => '2',
                'args' => $cpObj
            ),
            array(
                'reqtype' => 'exec',
                'module' => 'Email',
                'func' => 'listpopswithdisk',
                'apiversion' => '2',
                'args' => $obj
            ),
        );
        foreach ($data as $arr) {
            $encode = $arr;
            if (empty($arr['args'])) {
                array_pop($encode);
            } elseif (($arr['args'] instanceof Cpanel_Core_Object)) {
                $encode['args'] = $arr['args']->getAllDataRecursively();
            }
            $ljson = "<cpanelaction>\n" . json_encode($encode) . "\n</cpanelaction>";
            array_push($arr, $ljson);
            $rdata[] = $arr;
        }
        return $rdata;
    }
    /**
     * @dataProvider rObjData2
     * @depends      testEncodeQueryAcceptsGoodQueryData
     *               
     * @paramsunknown_type $reqtype  
     * @paramsunknown_type $module   
     * @paramsunknown_type $func     
     * @paramsunknown_type $apiverion
     * @paramsunknown_type $args     
     * @paramsunknown_type $expectE  
     */
    public function testEncodeQueryReturnsCpanelActionString($reqtype, $module, $func, $apiverion, $args, $ljson)
    {
        $rObj = $this->getRObj();
        $rObj->query->reqtype = $reqtype;
        $rObj->query->module = $module;
        $rObj->query->func = $func;
        $rObj->query->apiversion = $apiverion;
        $rObj->query->args = $args;
        $p = new $this->cut();
        $actual = $p->encodeQuery($rObj);
        $this->assertEquals($ljson, $actual);
    }
    /**
     * @dataProvider rObjData2
     * @depends      testEncodeQueryAcceptsGoodQueryData
     *               
     * @paramsunknown_type $reqtype  
     * @paramsunknown_type $module   
     * @paramsunknown_type $func     
     * @paramsunknown_type $apiverion
     * @paramsunknown_type $args     
     * @paramsunknown_type $expectE  
     */
    public function testEncodeQueryStoresActionInRObj($reqtype, $module, $func, $apiverion, $args, $ljson)
    {
        $rObj = $this->getRObj();
        $rObj->query->reqtype = $reqtype;
        $rObj->query->module = $module;
        $rObj->query->func = $func;
        $rObj->query->apiversion = $apiverion;
        $rObj->query->args = $args;
        $p = new $this->cut();
        $actual = $p->encodeQuery($rObj);
        $ptype = $p::PARSER_TYPE;
        $this->assertEquals($ljson, $rObj->query->$ptype);
    }
}
