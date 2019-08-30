<?php



class concreteRemoteQuery extends Cpanel_Query_Http_Abstract
{
    public function getAdapterResponseFormatType()
    {
    }
    public function setAdapterResponseFormatType($type)
    {
    }
}
/**
 * Test class for Cpanel_Query_Http_Abstract.
 * @covers Cpanel_Query_Http_Abstract
 */
class Cpanel_Query_Http_AbstractTest extends CpanelTestCase
{
    /**
     * @var Cpanel_Query_Http_Abstract
     */
    protected $rq;
    protected $cut = 'concreteRemoteQuery';
    protected $real_cut = 'Cpanel_Query_Http_Abstract';
    protected $qa = 'Cpanel_Query_Object';
    /**
     * 
     * Enter description here ...
     * @param unknown_type               $methods  
     * @param unknown_type               $args     
     * @param unknown_type               $mockName 
     * @param unknown_type               $callConst
     * @param unknown_type               $callClone
     * @param unknown_type               $callA    
     *                                               
     * @note   This method actually returns a Mock Class
     * 
     * @return Cpanel_Query_Http_Abstract
     */
    public function getRQ($methods = array(), $args = array(), $mockName = '', $callConst = true, $callClone = true, $callA = true)
    {
        if (empty($methods)) {
            $methods = null;
        }
        return $this->_makeMock($this->cut, $methods, $args, $mockName, $callConst, $callClone, $callA);
    }
    /** 
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
    /**
     * @expectException Exception
     */
    public function testConstructThrowsOnBadInput()
    {
        $this->expectException('Exception');
        $rq = $this->getRQ();
        $rmeth = new ReflectionMethod($this->cut, '__construct');
        $rmeth->invoke($rq, new stdClass());
    }
    public function testConstructCallsSetResponseObjectOnValidInput()
    {
        $rObj = $this->getRObj();
        $rq = $this->getRQ(array(
            'setResponseObject'
        ));
        $rq->expects($this->once())->method('setResponseObject')->with($rObj);
        $rmeth = new ReflectionMethod($this->cut, '__construct');
        $rmeth->invoke($rq, $rObj);
    }
    public function testHasAbstractMethods()
    {
        $expected = array(
            'getAdapterResponseFormatType',
            'setAdapterResponseFormatType'
        );
        sort($expected);
        $rclass = new ReflectionClass($this->real_cut);
        $methods = $rclass->getMethods();
        foreach ($methods as $meth) {
            if ($meth->isAbstract()) {
                $actual[] = $meth->getName();
            }
        }
        sort($actual);
        $this->assertEquals($expected, $actual);
    }
    /**
     * @expectException Exception
     */
    public function testSetResponseObjectThrowOnNonQueryObject()
    {
        $this->expectException('Exception');
        $rq = $this->getRQ();
        $rq->setResponseObject(new stdClass());
    }
    /**
     * Verify that a QueryObject can be stored
     */
    public function testSetResponseObjectDoesSomething()
    {
        $rObj = $this->getRObj();
        $mock = $this->_makeMock($this->cut, array(
            'setOptions'
        ));
        $mock->expects($this->once())->method('setOptions')->with(array(
            'responseObject' => $rObj
        ));
        $mock->setResponseObject($rObj);
    }
    /**
     * Verify we get the stored Response Object
     * @depends testSetResponseObjectDoesSomething
     */
    public function testGetResponseObject($fixtureArray)
    {
        $rObj = $this->getRObj();
        $rq = $this->getRQ();
        $rq->setResponseObject($rObj);
        $storedRObj = $rq->getResponseObject();
        $expected = spl_object_hash($rObj);
        $actual = spl_object_hash($storedRObj);
        $this->assertEquals($expected, $actual);
    }
    /**
     * Various tests to verify that initialization of core variables is possible
     */
    /**
     * Verify order of parameters
     */
    public function testInitParamOrder()
    {
        $rmeth = new ReflectionMethod($this->cut, 'init');
        $params = $rmeth->getParameters();
        $expected = array(
            0 => 'host',
            1 => 'user',
            2 => 'password',
            3 => 'overridePrev',
        );
        foreach ($params as $param) {
            $actual[$param->getPosition() ] = $param->getName();
        }
        $this->assertEquals($expected, $actual);
    }
    /**
     * Verify client type is checked
     * @depends testInitParamOrder
     */
    public function testInitCallsGetValidClientType()
    {
        $rq = $this->getRQ(array(
            'getValidClientType'
        ));
        $rq->expects($this->once())->method('getValidClientType')->will($this->returnValue($this->anything()));
        $rq->init('1.1.1.1', 'bar', 'baz');
    }
    /**
     * Verify client type is checked
     * @depends           testInitCallsGetValidClientType
     * @expectException Exception
     */
    public function testInitThrowsOnBadGetClientTypeReturn()
    {
        $this->expectException('Exception');
        $rq = $this->getRQ(array(
            'getValidClientType'
        ));
        $rq->expects($this->once())->method('getValidClientType')->will($this->returnValue(null));
        $rq->init('1.1.1.1', 'bar', 'baz');
    }
    public function testInitCallsSetProtocol()
    {
        $rq = $this->getRQ(array(
            'setProtocol'
        ));
        $rq->expects($this->once())->method('setProtocol')->will($this->returnValue($this->anything()));
        $rq->init('1.1.1.1', 'bar', 'baz');
    }
    /**
     * @depends           testInitParamOrder
     * @expectException Exception
     */
    public function testInitThrowsWithoutHost()
    {
        $this->expectException('Exception');
        $rq = $this->getRQ();
        $rq->init();
    }
    /**
     * attempts to store host
     * @depends         testInitParamOrder
     * @outputBuffering disabled
     */
    public function testInitStoresPrivateHost()
    {
        $rprop = new ReflectionProperty($this->cut, 'host');
        $rprop->setAccessible(true);
        $rq = $this->getRQ();
        $expected = '0.0.0.0';
        $rprop->setValue($rq, $expected);
        $rq->init('1.1.1.1');
        $actual = $rprop->getValue($rq);
        $this->assertNotEquals($expected, $actual);
    }
    /**
     * attempts to store user
     * @depends testInitParamOrder
     */
    public function testInitStoresPrivateUser()
    {
        $rprop = new ReflectionProperty($this->cut, 'user');
        $rprop->setAccessible(true);
        $rq = $this->getRQ();
        $expected = 'foo';
        $rprop->setValue($rq, $expected);
        $rq->init('1.1.1.1', 'bar');
        $actual = $rprop->getValue($rq);
        $this->assertNotEquals($expected, $actual);
    }
    /**
     * attempts to store password
     * @depends testInitParamOrder
     */
    public function testInitStoresPasswordAsPrivateAuth()
    {
        $rprop = new ReflectionProperty($this->cut, 'auth');
        $rprop->setAccessible(true);
        $rq = $this->getRQ();
        $expected = 'foo';
        $rprop->setValue($rq, $expected);
        $rq->init('1.1.1.1', 'bar', 'baz');
        $actual = $rprop->getValue($rq);
        $this->assertNotEquals($expected, $actual);
    }
    /**
     * Verify auth_type is set by init method
     * @depends testInitParamOrder
     */
    public function testInitSetsPrivateAuthType()
    {
        $rprop = new ReflectionProperty($this->cut, 'auth_type');
        $rprop->setAccessible(true);
        $rq = $this->getRQ();
        $expected = 'hash';
        $rprop->setValue($rq, $expected);
        $rq->init('1.1.1.1', 'bar', 'baz');
        $actual = $rprop->getValue($rq);
        $this->assertNotEquals($expected, $actual);
    }
    /**
     * Verify http_client is set
     * @depends testInitCallsGetValidClientType
     */
    public function testInitStoresPrivateHttpClient()
    {
        $rprop = new ReflectionProperty($this->cut, 'http_client');
        $rprop->setAccessible(true);
        $rq = $this->getRQ();
        $expected = '0.0.0.0';
        $rprop->setValue($rq, $expected);
        $rq->init('1.1.1.1');
        $actual = $rprop->getValue($rq);
        $this->assertNotEquals($expected, $actual);
    }
    public function testInitWillImmediatelyReturn()
    {
        $host = '1.1.1.1';
        $user = 'foo';
        $password = 'bar';
        $rObj = $this->getRObj();
        $rq = $this->getRQ(array(
            'setPassword'
        ));
        $rq->setResponseObject($rObj);
        $rq->expects($this->once())->method('setPassword');
        $rq->init($host, $user, $password);
        $rq->init($host, $user, $password);
    }
    public function testInitWillImmediatelyReturnAsRequested()
    {
        $host = '1.1.1.1';
        $user = 'foo';
        $password = 'bar';
        $rObj = $this->getRObj();
        $rq = $this->getRQ(array(
            'setPassword'
        ));
        $rq->setResponseObject($rObj);
        $rq->expects($this->exactly(2))->method('setPassword');
        $rq->init($host, $user, $password);
        $rq->init($host, $user, $password, true);
        $rq->init($host, $user, $password);
    }
    public function testLegacySetPortCallsSetPort()
    {
        $port = 2087;
        $rq = $this->getRQ(array(
            'setPort'
        ));
        $rq->expects($this->once())->method('setPort')->with($port);
        $rq->set_port($port);
    }
    public function portToProtoData()
    {
        return array(
            array(
                '2082',
                'http'
            ),
            array(
                '2086',
                'http'
            ),
            array(
                '2095',
                'http'
            ),
            array(
                '80',
                'http'
            ),
            array(
                '2087',
                'https'
            ),
            array(
                '2083',
                'https'
            ),
            array(
                '2096',
                'https'
            ),
        );
    }
    /**
     * @dataProvider portToProtoData
     *               Enter description here ...
     */
    public function testGetProtocolForPort($port, $proto)
    {
        $rmeth = new ReflectionMethod($this->cut, 'getProtocolForPort');
        $rmeth->setAccessible(true);
        $rq = $this->getRQ();
        $actual = $rmeth->invoke($rq, $port);
        $this->assertEquals($proto, $actual);
    }
    public function validSecurePorts()
    {
        return array(
            array(
                array(
                    2087,
                    2083,
                    2096
                )
            )
        );
    }
    public function validInsecurePorts()
    {
        return array(
            array(
                array(
                    80,
                    2086,
                    2082,
                    2095
                )
            )
        );
    }
    public function invalidPorts()
    {
        return array(
            array(
                array(
                    0,
                    65536,
                    666666
                )
            )
        );
    }
    /**
     * Verify port is set
     * @dataProvider validSecurePorts
     */
    public function testSetPortStoresSecurePrivatePort($ports)
    {
        foreach ($ports as $port) {
            $rprop = new ReflectionProperty($this->cut, 'port');
            $rprop->setAccessible(true);
            $rq = $this->getRQ();
            $base = '0001';
            $rprop->setValue($rq, $base);
            $rq->setPort($port);
            $actual = $rprop->getValue($rq);
            $this->assertEquals($port, $actual);
        }
    }
    /**
     * Verify port is set insecure ports
     * @dataProvider validInsecurePorts
     */
    public function testSetPortStoresInsecurePrivatePort($ports)
    {
        foreach ($ports as $port) {
            $rprop = new ReflectionProperty($this->cut, 'port');
            $rprop->setAccessible(true);
            $rq = $this->getRQ();
            $base = '0001';
            $rprop->setValue($rq, $base);
            $rq->setPort($port);
            $actual = $rprop->getValue($rq);
            $this->assertEquals($port, $actual);
        }
    }
    /**
     * Verify string port can be coerced and stored
     */
    public function testSetPortCoercesString()
    {
        $portStr = '2087';
        $rprop = new ReflectionProperty($this->cut, 'port');
        $rprop->setAccessible(true);
        $rq = $this->getRQ();
        $base = '0001';
        $rprop->setValue($rq, $base);
        $rq->setPort($portStr);
        $actual = $rprop->getValue($rq);
        $this->assertIsInt($actual);
    }
    /**
     * Verify only valid ports
     * @dataProvider invalidPorts
     *               NOTE: we can't use phpunits expectexception in combo w/ dataprovider
     *               without calling setExeception in the test method
     */
    public function testSetPortThrowOnInvalidPort($ports)
    {
        foreach ($ports as $port) {
            $rprop = new ReflectionProperty($this->cut, 'port');
            $rprop->setAccessible(true);
            $rq = $this->getRQ();
            $base = '0001';
            $rprop->setValue($rq, $base);
            try {
                $rq->setPort($port);
            }
            catch(Exception $e) {
                $eports[] = $port;
            }
            $actual = $rprop->getValue($rq);
            $this->assertNotEquals($port, $actual);
        }
        $this->assertEquals($ports, $eports);
    }
    /**
     * @expectException Exception
     */
    public function testSetPortThrowOnInvalidCalculatedProtocol()
    {
        $this->expectException('Exception');
        $port = 2086;
        $rprop = new ReflectionProperty($this->cut, 'port');
        $rprop->setAccessible(true);
        $rq = $this->getRQ(array(
            'getProtocolForPort'
        ));
        $rq->expects($this->any())->method('getProtocolForPort')->will($this->returnValue(false));
        $base = '0001';
        $rprop->setValue($rq, $base);
        $rq->setPort($port);
    }
    /**
     * Verify setProtocol is called by setPort
     */
    public function testSetPortCallsSetProtocol()
    {
        $rq = $this->getRQ(array(
            'setProtocol'
        ));
        $rq->expects($this->once())->method('setProtocol')->with($this->anything());
        $rq->setPort(2087);
    }
    /**
     * Verify legacy protocol method
     * Enter description here ...
     */
    public function testLegacySetProtocolCallsSetProtocol()
    {
        $proto = 'http';
        $rq = $this->getRQ(array(
            'setProtocol'
        ));
        $rq->expects($this->once())->method('setProtocol')->with($proto);
        $rq->set_protocol($proto);
    }
    /**
     * Verify throws on non 'http'|'https'
     * @expectException Exception
     */
    public function testSetProtocolThrowsOnInvalid()
    {
        $this->expectException('Exception');
        $rq = $this->getRQ();
        $rq->setProtocol('foo');
    }
    public function testSetProtocolStoresValidInput()
    {
        $protos = array(
            'http',
            'https'
        );
        foreach ($protos as $proto) {
            $rprop = new ReflectionProperty($this->cut, 'protocol');
            $rprop->setAccessible(true);
            $rq = $this->getRQ();
            $base = 'foo';
            $rprop->setValue($rq, $base);
            $rq->setProtocol($proto);
            $actual = $rprop->getValue($rq);
            $this->assertEquals($proto, $actual);
        }
    }
    /**
     * Verify set_output will call setOutput
     */
    public function testLegacySetOutputCallsSetOutput()
    {
        $o = 'json';
        $rq = $this->getRQ(array(
            'setOutput'
        ));
        $rq->expects($this->once())->method('setOutput')->with($o);
        $rq->set_output($o);
    }
    public function testLegacySetAuthTypeCallsSetAuthType()
    {
        $o = 'hash';
        $rq = $this->getRQ(array(
            'setAuthType'
        ));
        $rq->expects($this->once())->method('setAuthType')->with($o);
        $rq->set_auth_type($o);
    }
    /**
     * @expectException Exception
     */
    public function testSetAuthTypeThrowsOnBadInput()
    {
        $this->expectException('Exception');
        $rprop = new ReflectionProperty($this->cut, 'auth_type');
        $rprop->setAccessible(true);
        $rq = $this->getRQ();
        $rprop->setValue($rq, 'hash');
        $rq->setAuthType('foo');
    }
    public function testSetAuthType()
    {
        $rprop = new ReflectionProperty($this->cut, 'auth_type');
        $rprop->setAccessible(true);
        $rq = $this->getRQ();
        $expected = 'hash';
        $rprop->setValue($rq, 'foo');
        $rq->setAuthType($expected);
        $actual = $rprop->getValue($rq);
        $this->assertEquals($expected, $actual);
        $rq->setAuthType('token');
        $actual = $rprop->getValue($rq);
        $this->assertEquals('token', $actual);
    }
    public function testLegacySetPasswordCallsSetPassword()
    {
        $o = 'foo';
        $rq = $this->getRQ(array(
            'setPassword'
        ));
        $rq->expects($this->once())->method('setPassword')->with($o);
        $rq->set_password($o);
    }
    /**
     * 
     */
    public function testSetPasswordStoresPrivateAuth()
    {
        $rprop = new ReflectionProperty($this->cut, 'auth');
        $rprop->setAccessible(true);
        $rq = $this->getRQ();
        $expected = 'secret';
        $rprop->setValue($rq, 'foo');
        $rq->setPassword($expected);
        $actual = $rprop->getValue($rq);
        $this->assertEquals($expected, $actual);
    }
    public function testSetPasswordCallsSetAuthType()
    {
        $rq = $this->getRQ(array(
            'setAuthType'
        ));
        $rq->expects($this->once())->method('setAuthType')->with('pass');
        $rq->setPassword('foo');
    }
    public function testLegacySetHashCallsSetHash()
    {
        $o = 'foo';
        $rq = $this->getRQ(array(
            'setHash'
        ));
        $rq->expects($this->once())->method('setHash')->with($o);
        $rq->set_hash($o);
    }
    public function testSetHashStoresPrivateAuth()
    {
        $rprop = new ReflectionProperty($this->cut, 'auth');
        $rprop->setAccessible(true);
        $rq = $this->getRQ();
        $expected = 'hash';
        $rprop->setValue($rq, 'foo');
        $rq->setHash($expected);
        $actual = $rprop->getValue($rq);
        $this->assertEquals($expected, $actual);
    }
    public function testSetHashCallsSetAuthType()
    {
        $rq = $this->getRQ(array(
            'setAuthType'
        ));
        $rq->expects($this->once())->method('setAuthType')->with('hash');
        $rq->setHash('foo');
    }
    /**
     * @depends testSetHashStoresPrivateAuth
     */
    public function testSetHashCleansPrintChar()
    {
        $rq = $this->getRQ();
        $rq->setHash("foo\r\nbar\r\n\n\r");

        $reflector = new ReflectionObject($rq);
        $attribute = $reflector->getProperty('auth');
        $attribute->setAccessible(true);
        $value = $attribute->getValue($rq);
        $attribute->setAccessible(false);

        $this->assertEquals('foobar', $value);
    }
    public function testLegacySetUserCallsSetUser()
    {
        $rq = $this->getRQ(array(
            'setUser'
        ));
        $rq->expects($this->once())->method('setUser')->with('foo');
        $rq->set_user('foo');
    }
    public function testSetUserStoresPrivateUser()
    {
        $rprop = new ReflectionProperty($this->cut, 'user');
        $rprop->setAccessible(true);
        $rq = $this->getRQ();
        $expected = 'bar';
        $rprop->setValue($rq, 'foo');
        $rq->setUser($expected);
        $actual = $rprop->getValue($rq);
        $this->assertEquals($expected, $actual);
    }
    public function testLegacyHashAuthCallsHashAuth()
    {
        $rq = $this->getRQ(array(
            'hashAuth'
        ));
        $rq->expects($this->once())->method('hashAuth')->with('foo', 'bar');
        $rq->hash_auth('foo', 'bar');
    }
    public function testHashAuthCallsSetHashAndSetUser()
    {
        $rq = $this->getRQ(array(
            'setHash',
            'setUser'
        ));
        $rq->expects($this->once())->method('setUser')->with('foo')->will($this->returnValue($rq));
        $rq->expects($this->once())->method('setHash')->with('bar')->will($this->returnValue($rq));
        $rq->hashAuth('foo', 'bar');
    }
    public function testLegacyPasswordAuthCallsPasswordAuth()
    {
        $rq = $this->getRQ(array(
            'passwordAuth'
        ));
        $rq->expects($this->once())->method('passwordAuth')->with('foo');
        $rq->password_auth('foo', 'bar');
    }
    public function testPasswordAuthCallsSetPasswordAndSetUser()
    {
        $rq = $this->getRQ(array(
            'setPassword',
            'setUser'
        ));
        $rq->expects($this->once())->method('setPassword')->with('bar')->will($this->returnValue($rq));
        $rq->expects($this->once())->method('setUser')->with('foo')->will($this->returnValue($rq));
        $rq->passwordAuth('foo', 'bar');
    }
    /**
     * Method return_xml is deprecated and will fail
     */
    public function testReturn_xml()
    {
        $rq = $this->getRQ();
        $this->assertFalse(method_exists($rq, 'return_xml'));
    }
    /**
     * Method return_object is deprecated and will fail
     */
    public function testReturn_object()
    {
        $rq = $this->getRQ();
        $this->assertFalse(method_exists($rq, 'return_object'));
    }
    public function testLegacySetHttpClientCallsSetHttpClient()
    {
        $o = 'curl';
        $rq = $this->getRQ(array(
            'setHttpClient'
        ));
        $rq->expects($this->once())->method('setHttpClient')->with($o);
        $rq->set_http_client($o);
    }
    /**
     * @expectException Exception
     */
    public function testSetHttpClientWillThrowOnInvalidInput()
    {
        $this->expectException('Exception');
        $rq = $this->getRQ();
        $rq->setHttpClient('foo');
    }
    public function testSetHttpClientStoresPrivateHttpClient()
    {
        $rprop = new ReflectionProperty($this->cut, 'http_client');
        $rprop->setAccessible(true);
        $rq = $this->getRQ();
        $expected = 'curl';
        $rprop->setValue($rq, 'foo');
        $rq->setHttpClient($expected);
        $actual = $rprop->getValue($rq);
        $this->assertEquals($expected, $actual);
    }
    public function validURLStrings()
    {
        return array(
            array(
                array(
                    '/',
                    'more/fun',
                )
            )
        );
    }
    public function invalidURLStrings()
    {
        return array(
            array(
                array(
                    'http://',
                    'noway',
                )
            )
        );
    }
    /**
     * @dataProvider validURLStrings
     *               Enter description here ...
     */
    public function testIsUrlReturnsTrueForValidInput($strs)
    {
        foreach ($strs as $url) {
            $rq = $this->getRQ();
            $this->assertTrue($rq->isURL($url));
        }
    }
    /**
     * @dataProvider invalidURLStrings
     *               Enter description here ...
     */
    public function testIsUrlReturnsFalseForInvalidInput($strs)
    {
        foreach ($strs as $url) {
            $rq = $this->getRQ();
            $this->assertFalse($rq->isURL($url));
        }
    }
    public function urlParamStrData()
    {
        return array(
            array(
                'key=value',
                true,
                true,
            ),
            array(
                'keyvalue',
                true,
                false,
            ),
            array(
                array(
                    'key' => 'value'
                ),
                false,
                false,
            ),
            array(
                '?key',
                true,
                false,
            ),
        );
    }
    public function testIsURLParamStrStrictDefaultsIsFalse()
    {
        $rmeth = new ReflectionMethod($this->cut, 'isURLParamStr');
        $params = $rmeth->getParameters();
        foreach ($params as $param) {
            if ($param->getPosition() == 1) {
                $this->assertEquals('strict', $param->getName());
                $this->assertFalse($param->getDefaultValue());
            }
        }
    }
    /**
     * @dataProvider urlParamStrData
     */
    public function testIsURLParamStrTrueForValidInput($data, $unstrict, $strict)
    {
        $rq = $this->getRQ();
        $this->assertEquals($unstrict, $rq->isURLParamStr($data));
        $this->assertEquals($strict, $rq->isURLParamStr($data, true));
    }
    /**
     * @expectException Exception
     */
    public function testBuildURLThrowsOnBadQueryObject()
    {
        $this->expectException('Exception');
        $vars = array(
            'function' => 'version',
            'host' => '1.1.1.1',
            'port' => 2087,
            'protocol' => 'https',
            'queryType' => 'json',
            'expectedURL' => 'https://1.1.1.1:2087/json-api/version',
            'expectException' => false,
        );
        $rObj = $this->getRObj();
        $rObj->setResponseFormatType($vars['queryType']);
        $rq = $this->getRQ(array(
            'getResponseObject'
        ));
        $rq->setResponseObject($rObj);
        $rq->expects($this->any())->method('getResponseObject')->will($this->returnValue(new stdClass()));
        $rmeth['host'] = new ReflectionProperty($this->cut, 'host');
        $rmeth['port'] = new ReflectionProperty($this->cut, 'port');
        $rmeth['protocol'] = new ReflectionProperty($this->cut, 'protocol');
        foreach ($rmeth as $key => $meth) {
            if (array_key_exists($key, $vars)) {
                $meth->setAccessible(true);
                $meth->setValue($rq, $vars[$key]);
            }
        }
        $rq->buildURL($vars['function']);
    }
    public function buildURLData()
    {
        return array(
            array(
                array(
                    array(
                        'function' => 'version',
                        'host' => '1.1.1.1',
                        'port' => 2087,
                        'protocol' => 'https',
                        'queryType' => 'JSON',
                        'expectedURL' => 'https://1.1.1.1:2087/json-api/version',
                        'expectException' => false,
                    ),
                    array(
                        'function' => 'foobar',
                        'host' => '1.1.1.2',
                        'port' => 2095,
                        'protocol' => 'http',
                        'queryType' => 'XML',
                        'expectedURL' => 'http://1.1.1.2:2095/xml-api/foobar',
                        'expectException' => false,
                    ),
                    array(
                        'function' => 'foobar',
                        'host' => '',
                        'port' => 2095,
                        'protocol' => 'http',
                        'queryType' => 'XML',
                        'expectedURL' => 'http://1.1.1.2:2095/xml-api/foobar',
                        'expectException' => true,
                    ),
                )
            )
        );
    }
    /**
     * @dataProvider buildURLData
     *               
     * @depends      testInitStoresPrivateHost
     * @depends      testInitCallsSetProtocol
     * @depends      testGetProtocolForPort
     *               
     */
    public function testBuildURLReturnsURL($arraySet)
    {
        foreach ($arraySet as $vars) {
            $rObj = $this->getRObj();
            $rObj->setResponseFormatType($vars['queryType']);
            $rq = $this->getRQ();
            $rq->setResponseObject($rObj);
            $rmeth['host'] = new ReflectionProperty($this->cut, 'host');
            $rmeth['port'] = new ReflectionProperty($this->cut, 'port');
            $rmeth['protocol'] = new ReflectionProperty($this->cut, 'protocol');
            foreach ($rmeth as $key => $meth) {
                if (array_key_exists($key, $vars)) {
                    $meth->setAccessible(true);
                    $meth->setValue($rq, $vars[$key]);
                }
            }
            if ($vars['expectException']) {
                $this->expectException('Exception');
                $rq->buildURL($vars['function']);
            } else {
                $actual = $rq->buildURL($vars['function']);
                $this->assertEquals($vars['expectedURL'], $actual);
            }
        }
    }
    /**
     * @depends testBuildURLReturnsURL
     */
    public function testBuildURLWillAttemptToGetProtocolIfPreviouslyUndef()
    {
        $vars = array(
            'function' => 'foobar',
            'host' => '1.1.1.2',
            'port' => 2095,
            'protocol' => '',
            'queryType' => 'xml',
            'expectedURL' => 'https://1.1.1.2:2095/xml-api/foobar',
        );
        $rq = $this->getRQ(array(
            'setProtocol'
        ));
        $rObj = $this->getRObj();
        $rObj->setResponseFormatType($vars['queryType']);
        $rq->setResponseObject($rObj);
        $rq->expects($this->once())->method('setProtocol')
        //            ->with()
        ->will($this->returnValue('https'));
        $rmeth['host'] = new ReflectionProperty($this->cut, 'host');
        $rmeth['port'] = new ReflectionProperty($this->cut, 'port');
        $rmeth['protocol'] = new ReflectionProperty($this->cut, 'protocol');
        foreach ($rmeth as $key => $meth) {
            if (array_key_exists($key, $vars)) {
                $meth->setAccessible(true);
                $meth->setValue($rq, $vars[$key]);
            }
        }
        $actual = $rq->buildURL($vars['function']);
        $this->assertEquals($vars['expectedURL'], $actual);
    }
    public function testBuildAuthStrReturnsHashAuthStr()
    {
        $user = 'jeb';
        $auth_type = 'hash';
        $auth = 'foobarbazblurg';
        $rq = $this->getRQ();
        $rprop = new ReflectionProperty($this->cut, 'auth_type');
        $rprop->setAccessible(true);
        $rprop->setValue($rq, $auth_type);
        $rprop = new ReflectionProperty($this->cut, 'auth');
        $rprop->setAccessible(true);
        $rprop->setValue($rq, $auth);
        $rprop = new ReflectionProperty($this->cut, 'user');
        $rprop->setAccessible(true);
        $rprop->setValue($rq, $user);
        $expected = "Authorization: WHM ${user}:${auth}\r\n";
        $actual = $rq->buildAuthStr();
        $this->assertEquals($expected, $actual);
    }
    public function testBuildAuthStrReturnsPasswordAuthStr()
    {
        $user = 'jeb';
        $auth_type = 'pass';
        $auth = 'foobarbazblurg';
        $rq = $this->getRQ();
        $rprop = new ReflectionProperty($this->cut, 'auth_type');
        $rprop->setAccessible(true);
        $rprop->setValue($rq, $auth_type);
        $rprop = new ReflectionProperty($this->cut, 'auth');
        $rprop->setAccessible(true);
        $rprop->setValue($rq, $auth);
        $rprop = new ReflectionProperty($this->cut, 'user');
        $rprop->setAccessible(true);
        $rprop->setValue($rq, $user);
        $expected = "Authorization: Basic " . base64_encode($user . ':' . $auth) . "\r\n";
        $actual = $rq->buildAuthStr();
        $this->assertEquals($expected, $actual);
    }
    public function testBuildAuthStrReturnsTokenAuthStr()
    {
        $user = 'jeb';
        $auth_type = 'token';
        $token = 'MYTOKENSTR';
        $rq = $this->getRQ();
        $rq->setAuthType($auth_type);
        $rq->setUser($user);
        $rq->setToken($token);
        $expected = "Authorization: cpanel $user:$token\r\n";
        $actual = $rq->buildAuthStr();
        $this->assertEquals($expected, $actual);
    }
    /**
     * @expectException Exception
     */
    public function testBuildAuthStrThrowsOnBadAuthType()
    {
        $this->expectException('Exception');
        $user = 'jeb';
        $auth_type = 'badinput';
        $auth = 'foobarbazblurg';
        $rq = $this->getRQ();

        $rq->setAuthType($auth_type);
        $rq->setUser($user);
        $rq->setHash($auth);

        $expected = "Authorization: Basic " . base64_encode($user . ':' . $auth) . "\r\n";
        $actual = $rq->buildAuthStr();
        $this->assertEquals($expected, $actual);
    }
    /**
     * @expectException Exception
     */
    public function testExecThrowsOnBadInput()
    {
        $this->expectException('Exception');
        $rq = $this->getRQ();
        $rq->exec(new stdClass());
    }
    /**
     * @expectException Exception
     */
    public function testExecThrowsOnUndefinedQueryClient()
    {
        $this->expectException('Exception');
        $rq = $this->getRQ();
        $rObj = $this->getRObj();
        $rObj->setQuery(array(
            'client' => null
        ));
        $rq->exec($rObj);
    }
    /**
     * @expectException Exception
     */
    public function testExecThrowsOnUndefinedQueryClientMethod()
    {
        $this->expectException('Exception');
        $rq = $this->getRQ();
        $rObj = $this->getRObj();
        $rObj->setQuery(array(
            'client' => 'foo'
        ));
        $rq->exec($rObj);
    }
    public function testExecCallsQueryClientMethod()
    {
        $rObj = $this->getRObj(true, array(
            'parse'
        ));
        $rObj->setResponseFormatType('json');
        $rObj->setQuery(array(
            'client' => 'curl'
        ));
        $rObj->expects($this->any())->method('parse')->will($this->returnValue($this->anything()));
        $rq = $this->getRQ(array(
            'curlQuery'
        ));
        $rq->expects($this->once())->method('curlQuery')->with($rObj)->will($this->returnValue('{"version":"12.01.01"}'));
        $rq->exec($rObj);
    }
    public function testExecCallsQueryObjectParse()
    {
        $rawstr = '{"version":"12.01.01"}';
        $rObj = $this->getRObj(true, array(
            'parse'
        ));
        $rObj->setResponseFormatType('json');
        $rObj->setQuery(array(
            'client' => 'curl'
        ));
        $rObj->expects($this->once())->method('parse')->with($rawstr)->will($this->returnValue($this->anything()));
        $rq = $this->getRQ(array(
            'curlQuery'
        ));
        $rq->expects($this->once())->method('curlQuery')->with($rObj)->will($this->returnValue($rawstr));
        $rq->exec($rObj);
    }
    public function testExecReturnsQueryObject()
    {
        $rawstr = '{"version":"12.01.01"}';
        $rObj = $this->getRObj();
        $rObj->setResponseFormatType('json');
        $rObj->setQuery(array(
            'client' => 'curl'
        ));
        $rq = $this->getRQ(array(
            'curlQuery'
        ));
        $rq->expects($this->once())->method('curlQuery')->with($this->anything())->will($this->returnValue($rawstr));
        $actual = $rq->exec($rObj);
        $this->assertInstanceOf('Cpanel_Query_Object', $actual);
    }
    public function testMakeQueryParameters()
    {
        $rmeth = new ReflectionMethod($this->cut, 'makeQuery');
        $params = $rmeth->getParameters();
        foreach ($params as $param) {
            $actual[$param->getPosition() ] = $param->getName();
        }
        $expected = array(
            'function',
            'vars'
        );
        $this->assertEquals($expected, $actual);
    }
    public function getMakeQueryFixture($user = null, $auth_type = null, $auth = null, $stubExtra = array(
        'curlExec',
        'exec'
    ))
    {
        $user = (is_null($user)) ? 'jeb' : $user;
        $auth_type = (is_null($auth_type)) ? 'hash' : $auth_type;
        $auth = (is_null($auth)) ? 'foobarbazblurg' : $auth;
        $methods = ($stubExtra) ? $stubExtra : array(
            'curlExec'
        );
        $rq = $this->getRQ($methods);
        $rObj = $this->getRObj();
        $rObj->setResponseFormatType('json');
        $rprop = new ReflectionProperty($this->cut, 'auth_type');
        $rprop->setAccessible(true);
        $rprop->setValue($rq, $auth_type);
        $rprop = new ReflectionProperty($this->cut, 'auth');
        $rprop->setAccessible(true);
        $rprop->setValue($rq, $auth);
        $rprop = new ReflectionProperty($this->cut, 'user');
        $rprop->setAccessible(true);
        $rprop->setValue($rq, $user);
        $rq->setResponseObject($rObj);
        if (in_array('exec', $stubExtra)) {
            $rq->expects($this->any())->method('exec')->will($this->returnValue($rObj));
        }
        $rq->expects($this->any())->method('curlExec')->will($this->returnValue(true));
        return array(
            $rq,
            $rObj
        );
    }
    /**
     * this is just a check against our setup to ensure all exceptions within class aren't throwing
     * and ancillary methods are mocked appropriately
     */
    public function testMakeQueryFixtureSanity()
    {
        list($rq, $rObj) = $this->getMakeQueryFixture();
        $rq->makeQuery('version');
        $this->expectNotToPerformAssertions();
    }
    /**
     * @expectException Exception
     */
    public function testMakeQueryThrowsOnEmptyInputFunctionVariable()
    {
        $this->expectException('Exception');
        list($rq, $rObj) = $this->getMakeQueryFixture();
        $rq->makeQuery('');
    }
    /**
     * @expectException Exception
     */
    public function testMakeQueryThrowsOnUndefinedPrivateUser()
    {
        $this->expectException('Exception');
        list($rq, $rObj) = $this->getMakeQueryFixture(false);
        $rq->makeQuery('version');
    }
    /**
     * @expectException Exception
     */
    public function testMakeQueryThrowsOnUndefinedPrivateAuth()
    {
        $this->expectException('Exception');
        list($rq, $rObj) = $this->getMakeQueryFixture(null, null, false);
        $rq->makeQuery('version');
    }
    /**
     * 
     * Enter description here ...
     */
    public function testMakeQueryCallsIsUrl()
    {
        $methods = array(
            'curlExec',
            'exec',
            'isURL'
        );
        list($rq, $rObj) = $this->getMakeQueryFixture(null, null, null, $methods);
        $rq->expects($this->once())->method('isURL')->will($this->returnValue(false));
        $rq->makeQuery('version');
    }
    public function testMakeQueryCallsIsURLParamStrForURLQuerys()
    {
        $methods = array(
            'curlExec',
            'exec',
            'isURL',
            'isURLParamStr'
        );
        list($rq, $rObj) = $this->getMakeQueryFixture(null, null, null, $methods);
        $rq->expects($this->any())->method('isURL')->will($this->returnValue(true));
        $rq->expects($this->once())->method('isURLParamStr')->will($this->returnValue(true));
        $rq->makeQuery('version');
    }
    public function testMakeQueryCallsBuildAuthStr()
    {
        $methods = array(
            'curlExec',
            'exec',
            'buildURL'
        );
        list($rq, $rObj) = $this->getMakeQueryFixture(null, null, null, $methods);
        $rq->expects($this->once())->method('buildURL')->will($this->returnValue(true));
        $rq->makeQuery('version');
    }
    public function testMakeQueryCallsGetResponseObject()
    {
        $methods = array(
            'curlExec',
            'exec',
            'getResponseObject'
        );
        list($rq, $rObj) = $this->getMakeQueryFixture(null, null, null, $methods);
        $rq->expects($this->any())->method('getResponseObject')->will($this->returnValue($rObj));
        $rq->makeQuery('version');
        $this->expectNotToPerformAssertions();
    }
    /**
     * @expectException Exception
     */
    public function testMakeQueryWillThrowOnBadResponseObject()
    {
        $this->expectException('Exception');
        $methods = array(
            'curlExec',
            'exec',
            'isURL',
            'buildURL',
            'buildAuthStr',
            'getResponseObject'
        );
        list($rq, $rObj) = $this->getMakeQueryFixture(null, null, null, $methods);
        //        $rq->setOptions(array('responseObject', new stdClass()));
        $rq->expects($this->any())->method('isURL')->will($this->returnValue(true));
        $rq->expects($this->any())->method('buildURL')->will($this->returnValue('https://1.1.1.1:2087/xml-api/verision'));
        $rq->expects($this->any())->method('buildAuthStr')->will($this->returnValue('Authentication Basic: blahkfaieijrlasjdfk'));
        $rq->expects($this->any())->method('getResponseObject')->will($this->returnValue(new stdClass()));
        $rq->makeQuery('version');
    }
    public function testMakeQueryInjectsListnerIntoQueryObject()
    {
        list($rq, $rObj) = $this->getMakeQueryFixture();
        $listner = new stdClass();
        $rq->listner = $listner;
        $rq->makeQuery('version');
        $expected = spl_object_hash($listner);
        $rObjlistner = $rObj->getOption('listner');
        if (!is_object($rObjlistner)) {
            $this->fail(__FUNCTION__ . ' did not inject listner into QueryObject');
        } else {
            $actual = spl_object_hash($rObjlistner);
            $this->assertEquals($expected, $actual);
        }
    }
    public function testMakeQueryStoresQueryVariables()
    {
        $methods = array(
            'curlExec',
            'exec',
            'getResponseObject'
        );
        list($rq, $rObj) = $this->getMakeQueryFixture('jeb', 'hash', 'foobarbazblurg');
        $rq->makeQuery('version');
        $rprop = new ReflectionProperty($this->cut, 'user');
        $rprop->setAccessible(true);
        $this->assertEquals('jeb', $rprop->getValue($rq));
    }
    public function testMakeQueryCallsExec()
    {
        list($rq, $rObj) = $this->getMakeQueryFixture();
        $rq->expects($this->once())->method('exec')->will($this->returnValue($rObj));
        $rq->makeQuery('version');
    }
    public function testMakeQueryReturnsQueryObject()
    {
        list($rq, $rObj) = $this->getMakeQueryFixture();
        $this->assertInstanceOf('Cpanel_Query_Object', $rq->makeQuery('version'));
    }
    public function testBuildCurlHeadersHasTwoArgs()
    {
        $rmeth = new ReflectionMethod($this->cut, 'buildCurlHeaders');
        $params = $rmeth->getParameters();
        $expected = array(
            0 => 'curl',
            1 => 'rObj',
        );
        foreach ($params as $param) {
            $actual[$param->getPosition() ] = $param->getName();
        }
        $this->assertEquals($expected, $actual);
    }
    public function testAddCurlPostFieldsParams()
    {
        $rmeth = new ReflectionMethod($this->cut, 'addCurlPostFields');
        $params = $rmeth->getParameters();
        $expected = array(
            0 => 'curl',
            1 => 'headerStr',
            2 => 'postdata'
        );
        foreach ($params as $param) {
            $actual[$param->getPosition() ] = $param->getName();
        }
        $this->assertEquals($expected, $actual);
    }
    /**
     * @expectException Exception
     */
    public function testAddCurlPostFieldsThrowOnInvalidResource()
    {
        $this->expectException('Exception');
        $rq = $this->getRQ();
        $rmeth = new ReflectionMethod($this->cut, 'addCurlPostFields');
        $rmeth->setAccessible(true);
        $rmeth->invoke($rq, new stdClass(), '', '');
    }
    public function testAddCurlPostFieldsReturnsString()
    {
        $rq = $this->getRQ();
        $rmeth = new ReflectionMethod($this->cut, 'addCurlPostFields');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($rq, curl_init(), '', 'what=ever');
        $this->assertIsString($actual);
    }
    /**
     * @expectException Exception
     */
    public function testBuildCurlHeadersThrowsOnBadInput()
    {
        $this->expectException('Exception');
        $user = 'foo';
        $pass = 'bar';
        $postdata = 'what=ever';
        $authstr = 'Authentication: Basic ' . base64_encode($user . ':' . $pass) . "\r\n";
        $customHeaders = array(
            'CustomHeader' => 'CustomHeaderValue'
        );
        $httpQueryType = 'POST';
        $url = 'https://1.1.1.1:2087/blurg';
        $curl = curl_init();
        $rq = $this->getRQ();
        $rObj = $this->getRObj();
        $rObj->query->authstr = $authstr;
        $rObj->query->url = $url;
        $rObj->query->args = $postdata;
        $rmeth = new ReflectionMethod($this->cut, 'buildCurlHeaders');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($rq, $curl, new stdClass());
    }
    public function testBuildCurlHeadersReturnsArray()
    {
        $user = 'foo';
        $pass = 'bar';
        $postdata = 'what=ever';
        $authstr = 'Authentication: Basic ' . base64_encode($user . ':' . $pass) . "\r\n";
        $customHeaders = array(
            'CustomHeader' => 'CustomHeaderValue'
        );
        $httpQueryType = 'POST';
        $url = 'https://1.1.1.1:2087/blurg';
        $curl = curl_init();
        $rq = $this->getRQ();
        $rObj = $this->getRObj();
        $rObj->query->authstr = $authstr;
        $rObj->query->url = $url;
        $rmeth = new ReflectionMethod($this->cut, 'buildCurlHeaders');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($rq, $curl, $rObj);
        $this->assertIsArray($actual);
    }
    public function testBuildCurlHeadersDoesNotAlterURLForPOST()
    {
        $user = 'foo';
        $pass = 'bar';
        $postdata = 'what=ever';
        $authstr = 'Authentication: Basic ' . base64_encode($user . ':' . $pass) . "\r\n";
        $customHeaders = array(
            'CustomHeader' => 'CustomHeaderValue'
        );
        $httpQueryType = 'POST';
        $url = 'https://1.1.1.1:2087/blurg';
        $curl = curl_init();
        $rq = $this->getRQ();
        $rObj = $this->getRObj();
        $rObj->query->authstr = $authstr;
        $rObj->query->url = $url;
        $rObj->query->args = $postdata;
        $rmeth = new ReflectionMethod($this->cut, 'buildCurlHeaders');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($rq, $curl, $rObj);
        $this->assertEquals($url, $rObj->query->url);
    }
    /**
     * @depends testAddCurlPostFieldsReturnsString
     */
    public function testBuildCurlHeadersCallsAddCurlPostFields()
    {
        $user = 'foo';
        $pass = 'bar';
        $postdata = 'what=ever';
        $authstr = 'Authentication: Basic ' . base64_encode($user . ':' . $pass) . "\r\n";
        $customHeaders = array(
            'CustomHeader' => 'CustomHeaderValue'
        );
        $httpQueryType = 'POST';
        $url = 'https://1.1.1.1:2087/blurg';
        $curl = curl_init();
        $rq = $this->getRQ(array(
            'addCurlPostFields'
        ));
        $rq->expects($this->once())->method('addCurlPostFields')->with($curl, $this->anything(), $postdata)->will($this->returnValue($this->anything()));
        $rObj = $this->getRObj();
        //        $rObj->setQuery($customHeaders);
        $rObj->query->authstr = $authstr;
        $rObj->query->url = $url;
        $rObj->query->args = $postdata;
        //        $rObj->query->setOptions(array('httpHeaders' => $customHeaders));
        $rmeth = new ReflectionMethod($this->cut, 'buildCurlHeaders');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($rq, $curl, $rObj);
        $this->assertEquals($url, $rObj->query->url);
    }
    public function testBuildCurlHeadersAddsCustomHeaders()
    {
        $user = 'foo';
        $pass = 'bar';
        $postdata = 'what=ever';
        $authstr = 'Authentication: Basic ' . base64_encode($user . ':' . $pass) . "\r\n";
        $customHeaders = array(
            'CustomHeader' => 'CustomHeaderValue'
        );
        $httpQueryType = 'POST';
        $url = 'https://1.1.1.1:2087/blurg';
        $curl = curl_init();
        $rq = $this->getRQ();
        $rObj = $this->getRObj();
        $rObj->query->httpHeaders = $customHeaders;
        $rObj->query->authstr = $authstr;
        $rObj->query->url = $url;
        $rObj->query->args = $postdata;
        $rmeth = new ReflectionMethod($this->cut, 'buildCurlHeaders');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($rq, $curl, $rObj);
        $containsHeaders = (bool)strpos($actual[0], 'CustomHeader: CustomHeaderValue');
        $this->assertTrue($containsHeaders);
    }
    public function testBuildCurlHeadersAltersURLForGET()
    {
        $user = 'foo';
        $pass = 'bar';
        $postdata = 'what=ever';
        $authstr = 'Authentication: Basic ' . base64_encode($user . ':' . $pass) . "\r\n";
        $customHeaders = array(
            'CustomHeader' => 'CustomHeaderValue'
        );
        $httpQueryType = 'GET';
        $url = 'https://1.1.1.1:2087/blurg';
        $curl = curl_init();
        $rq = $this->getRQ();
        $rObj = $this->getRObj();
        $rObj->query->httpQueryType = $httpQueryType;
        $rObj->query->authstr = $authstr;
        $rObj->query->url = $url;
        $rObj->query->args = $postdata;
        $rmeth = new ReflectionMethod($this->cut, 'buildCurlHeaders');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($rq, $curl, $rObj);
        $this->assertEquals("{$url}?{$postdata}", $rObj->query->url);
    }
    /**
     * @expectException Exception
     */
    public function testCurlExecRequiresResource()
    {
        $this->expectException('Exception');
        $rq = $this->getRQ();
        $rmeth = new ReflectionMethod($this->cut, 'curlExec');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($rq, new stdClass());
    }
    /**
     * @expectException Exception
     */
    public function testCurlExecThrowsOnBadCall()
    {
        $this->expectException('Exception');
        $rq = $this->getRQ();
        $rmeth = new ReflectionMethod($this->cut, 'curlExec');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($rq, curl_init());
    }
    /**
     * @expectException Exception
     */
    public function testCurlExecThrowsOnBadCallDetailsRObj()
    {
        $this->expectException('Exception');
        $postdata = 'what=ever';
        $url = 'blurg';
        $rq = $this->getRQ();
        $rObj = $this->getRObj();
        $rObj->query->url = $url;
        $rObj->query->args = $postdata;
        $rq->setResponseObject($rObj);
        $rmeth = new ReflectionMethod($this->cut, 'curlExec');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($rq, curl_init());
    }
    /**
     * @expectException Exception
     */
    public function testCurlQueryThrowsOnBadInput()
    {
        $this->expectException('Exception');
        $rObj = $this->getRObj();
        $rq = $this->getRQ(array(
            'buildCurlHeaders'
        ));
        $rq->curlQuery(new stdClass());
    }
    /**
     * @expectException Exception
     */
    public function testCurlQueryThrowsOnBadCall()
    {
        $this->expectException('Exception');
        $rObj = $this->getRObj();
        $rq = $this->getRQ(array(
            'buildCurlHeaders'
        ));
        $rq->expects($this->once())->method('buildCurlHeaders')->with($this->anything(), $rObj)->will($this->returnValue(array()));
        $rq->curlQuery($rObj);
    }
    public function testCurlQueryCallsCurlExec()
    {
        $rObj = $this->getRObj();
        $rq = $this->getRQ(array(
            'curlExec',
            'buildCurlHeaders'
        ));
        $rq->expects($this->any())->method('buildCurlHeaders')->with($this->anything(), $rObj)->will($this->returnValue(array()));
        $rq->expects($this->once())->method('curlExec')->will($this->returnValue(true));
        $rq->curlQuery($rObj);
    }
    public function testCurlQueryAttemptsToLogData()
    {
        $listner = $this->_makeMock('Cpanel_Listner_Subject_Logger', array(
            'log'
        ));
        $listner->expects($this->once())->method('log');
        $rObj = $this->getRObj();
        $rq = $this->getRQ(array(
            'curlExec',
            'buildCurlHeaders'
        ));
        Cpanel_Listner_Observer_GenericLogger::initLogger($rq, 1, array(
            'level' => 'std'
        ), $listner);
        $rq->expects($this->any())->method('buildCurlHeaders')->with($this->anything(), $rObj)->will($this->returnValue(array()));
        $rq->expects($this->any())->method('curlExec')->will($this->returnValue(true));
        $rq->curlQuery($rObj);
    }
    /**
     * @depends testCurlQueryCallsCurlExec
     */
    public function testCurlQueryCallsBuildCurlHeaders()
    {
        $rObj = $this->getRObj();
        $rq = $this->getRQ(array(
            'buildCurlHeaders',
            'curlExec'
        ));
        $rq->expects($this->once())->method('buildCurlHeaders')->with($this->anything(), $rObj)->will($this->returnValue(array()));
        $rq->expects($this->any())->method('curlExec')->will($this->returnValue(true));
        $rq->curlQuery($rObj);
    }
    /**
     * @expectException Exception
     */
    public function testFopenQueryThrowsOnBadInput()
    {
        $this->expectException('Exception');
        $url = 'http://1.1.1.1:2086/xml-api/version';
        $proto = 'http';
        $rObj = $this->getRObj();
        $rObj->query->url = $url;
        $rq = $this->getRQ(array(
            'getProtocol',
            'fopenExec'
        ));
        $rq->setResponseObject($rObj);
        $rq->expects($this->any())->method('getProtocol')->will($this->returnValue($proto));
        $rq->expects($this->any())->method('fopenExec')->will($this->returnValue($this->anything()));
        $rq->fopenQuery(new stdClass());
    }
    /**
     * @expectException Exception
     */
    public function testBuildFopenContextOptsThrowsOnBadInput()
    {
        $this->expectException('Exception');
        $rq = $this->getRQ();
        $rmeth = new ReflectionMethod($this->cut, 'buildFopenContextOpts');
        $rmeth->setAccessible(true);
        $rmeth->invoke($rq, new stdClass());
    }
    public function testBuildFopenContextOptsReturnsArray()
    {
        $rObj = $this->getRObj();
        $rq = $this->getRQ();
        $rmeth = new ReflectionMethod($this->cut, 'buildFopenContextOpts');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($rq, $rObj);
        $this->assertIsArray($actual);
    }
    public function testBuildFopenContextExercisesOptsValues()
    {
        $user = 'foo';
        $pass = 'bar';
        $postdata = 'what=ever';
        $authstr = 'Authentication: Basic ' . base64_encode($user . ':' . $pass) . "\r\n";
        $customHeaders = array(
            'CustomHeader' => 'CustomHeaderValue'
        );
        $httpQueryType = 'GET';
        $url = 'https://1.1.1.1:2087/blurg';
        $rq = $this->getRQ();
        $rObj = $this->getRObj();
        $rObj->query->httpHeaders = $customHeaders;
        $rObj->query->authstr = $authstr;
        $rObj->query->url = $url;
        $rObj->query->args = $postdata;
        $rObj->query->httpQueryType = $httpQueryType;
        $rmeth = new ReflectionMethod($this->cut, 'buildFopenContextOpts');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($rq, $rObj);
        $containsHeaders = (bool)strpos($actual['http']['header'], 'CustomHeader: CustomHeaderValue');
        $this->assertTrue($containsHeaders);
        $this->assertEquals($httpQueryType, $actual['http']['method']);
    }
    public function fopenQueryData()
    {
        return array(
            array(
                'https://1.1.1.1:2087/blurg',
                'https',
                true
            ),
            array(
                'http://1.1.1.1:2086/blurg',
                'http',
                true
            ),
            array(
                'https://1.1.1.1:2087/blurg',
                'http',
                false
            ),
            array(
                'ftp://1.1.1.1:notchecked/blurg',
                'ftp',
                true
            ), //will fail in functional context ;)
            array(
                'ftp://1.1.1.1:notchecked/blurg',
                'http',
                false
            ),
            array(
                '/blurg',
                'http',
                false
            ),
        );
    }
    /**
     * @dataProvider fopenQueryData
     *               
     *               As noted in the dataprovider, this method is really only concerned with
     *               the proper protocol being register AND the URL's protcol being in sync
     *               with that stored protocol type.  It would be weird to have the stored
     *               proto and the URL's proto be off, but better safe...
     *               
     */
    public function testFopenQueryCallsGetProtocol($url, $proto, $valid)
    {
        $rObj = $this->getRObj();
        $rObj->query->url = $url;
        $rq = $this->getRQ(array(
            'getProtocol',
            'fopenExec'
        ));
        $rq->expects($this->once())->method('getProtocol')->will($this->returnValue($proto));
        $rq->expects($this->any())->method('fopenExec')->will($this->returnValue($this->anything()));
        if (!$valid) {
            $this->expectException('Exception');
        }
        $rq->fopenQuery($rObj);
    }
    public function testFopenQueryCallsBuildFopenContextOpts()
    {
        $url = "https://1.1.1.1:2087/xml-api/version";
        $proto = 'https';
        $rObj = $this->getRObj();
        $rObj->query->url = $url;
        $rq = $this->getRQ(array(
            'getProtocol',
            'fopenExec',
            'buildFopenContextOpts'
        ));
        $rq->expects($this->any())->method('getProtocol')->will($this->returnValue($proto));
        $rq->expects($this->any())->method('fopenExec')->will($this->returnValue($this->anything()));
        $rq->expects($this->any())->method('buildFopenContextOpts')->with($rObj)->will($this->returnValue(array()));
        $rq->fopenQuery($rObj);
        $this->expectNotToPerformAssertions();
    }
    /**
     * @expectException Exception
     */
    public function testFopenExecThrowsOnInvalidStreamContext()
    {
        $this->expectException('Exception');
        $url = 'https://1.1.1.1:2087/xml-api/version';
        $context = '';
        $rq = $this->getRQ();
        $rmeth = new ReflectionMethod($this->cut, 'fopenExec');
        $rmeth->setAccessible(true);
        $rmeth->invoke($rq, $url, false, $context);
    }
    /**
     * @expectException Exception
     */
    public function testFopenExecThrowsOnNonURL()
    {
        $this->expectException('Exception');
        $url = 'https://';
        $context = stream_context_create(array());
        $rq = $this->getRQ();
        $rmeth = new ReflectionMethod($this->cut, 'fopenExec');
        $rmeth->setAccessible(true);
        $rmeth->invoke($rq, $url, false, $context);
    }
    public function testMagicCallReturnsCpanelObjectValueFromGetUnderscoreMethod()
    {
        $rq = $this->getRQ(array(
            'getOption'
        ));
        $rq->expects($this->once())->method('getOption')->with('foo');
        $rq->get_foo();
    }
    public function testMagicCallReturnsProtectedValueFromGetUnderscoreMethod()
    {
        $rq = $this->getRQ(array(
            'getOption'
        ));
        $rq->expects($this->never())->method('getOption');
        $this->assertEquals('127.0.0.1', $rq->get_host());
    }
    public function testMagicCallReturnsProtectedValueFromGetNoUnderscoreMethod()
    {
        $rq = $this->getRQ(array(
            'getOption'
        ));
        $rq->expects($this->never())->method('getOption');
        $this->assertEquals('127.0.0.1', $rq->getHost());
    }
    public function testMagicCallReturnsCpanelObjectValueFromGetNoUnderscoreMethod()
    {
        $rq = $this->getRQ(array(
            'getOption'
        ));
        $rq->expects($this->once())->method('getOption')->with('foo')->will($this->returnValue('bar'));
        $this->assertEquals('bar', $rq->getFoo());
    }
    public function testMagicCallInvokesSetMethodFromSetUnderscoreMethod()
    {
        $value = 'bar';
        $rq = $this->getRQ(array(
            'setFoo'
        ));
        $rq->expects($this->once())->method('setFoo')->with($value);
        $rq->set_foo($value);
    }
    public function testMagicCallInvokesSetMethodFromSetMultiUnderscoreMethod()
    {
        $value = 'bar';
        $rq = $this->getRQ(array(
            'setFooBaz'
        ));
        $rq->expects($this->once())->method('setFooBaz')->with($value);
        $rq->set_foo_baz($value);
    }
    /**
     * @outputBuffering disables
     */
    public function testMagicCallSetsNonPrivateNonSetterVariableToCpanelObjectStore()
    {
        $arr = array( 'foo' => 'bar');
        $rq = $this->getRQ(array(
            'setOptions'
        ));
        $rq->expects($this->once())->method('setOptions')->with($arr)->will($this->returnArgument(0));
        $actual = $rq->set_foo($arr['foo']);
    }
    /**
     * @expectException Exception
     */
    public function testMagicCallThrowsOnNonGetOrSetMethodCall()
    {
        $this->expectException('Exception');
        $rq = $this->getRQ(array(
            'setOptions',
            'getOption',
            'setFoo'
        ));
        $rq->expects($this->never())->method('setOptions');
        $rq->expects($this->never())->method('getOption');
        $rq->expects($this->never())->method('setFoo');
        $rq->foo();
    }
}
?>
