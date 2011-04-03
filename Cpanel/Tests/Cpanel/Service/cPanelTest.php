<?php
/**
 * @covers Cpanel_Service_cPanel
 * @author davidneimeyer
 *         
 */
class Cpanel_Service_cPanelTest extends PHPUnit_Framework_TestCase
{
    protected $cut = 'Cpanel_Service_cPanel';
    protected $qa = 'Cpanel_Query_Object';
    /**
     * 
     * Enter description here ...
     * @param unknown_type          $methods  
     * @param unknown_type          $args     
     * @param unknown_type          $mockName 
     * @param unknown_type          $callConst
     * @param unknown_type          $callClone
     * @param unknown_type          $callA    
     *                                          
     * @note   This method actually returns a Mock Class
     * 
     * @return Cpanel_Service_cPanel
     */
    public function getCP($methods = array(), $args = array(), $mockName = '', $callConst = true, $callClone = true, $callA = true)
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
    public function getOptsArray()
    {
        $obj = new stdClass();
        Cpanel_Listner_Observer_GenericLogger::initLogger($obj, 1, array(
            'level' => 'silent'
        ));
        return array(
            'host' => '1.1.1.1',
            'user' => 'foo',
            'listner' => $obj->listner
        );
    }
    public function setLocalEnviro()
    {
        $file = '/tmp/publicapi.test.sock';
        touch($file);
        putenv('CPANEL_PHPCONNECT_SOCKET=' . $file);
    }
    public function unsetLocalEnviro()
    {
        $file = '/tmp/publicapi.test.sock';
        if (file_exists($file)) {
            unlink($file);
        }
        putenv('CPANEL_PHPCONNECT_SOCKET');
    }
    public function testCanInstantiateClean()
    {
        $cp = new $this->cut();
        $this->assertInstanceOf($this->cut, $cp);
    }
    public function testConstantAdapterDefaultIsDefined()
    {
        $classname = $this->cut;
        $cp = new $classname($this->getOptsArray());
        $this->assertTrue(defined("{$classname}::ADAPTER_DEFAULT"));
        $this->assertEquals('cpanel', $classname::ADAPTER_DEFAULT);
    }
    /**
     * @depends testConstantAdapterDefaultIsDefined
     */
    public function testGetDefaultAdapterNameReturnsConstantValue()
    {
        $classname = $this->cut;
        $cp = new $classname($this->getOptsArray());
        $this->assertEquals($classname::ADAPTER_DEFAULT, $cp->getDefaultAdapterName());
    }
    public function testConstructorWillSetOptions()
    {
        $expected = self::getOptsArray(); //
        $cp = new $this->cut($expected);
        $vars = array(
            'host' => $cp->getOption('host'),
            'user' => $cp->getOption('user'),
        );
        foreach ($vars as $key => $value) {
            $this->assertEquals($expected[$key], $value);
        }
    }
    public function testConstructorWillSetOptionsWithConfigNamespaceOffset()
    {
        $expected = array(
            'config' => self::getOptsArray()
        );
        $cp = new $this->cut($expected);
        $vars = array(
            'host' => $cp->getOption('host'),
            'user' => $cp->getOption('user'),
        );
        foreach ($vars as $key => $value) {
            $this->assertEquals($expected['config'][$key], $value);
        }
    }
    /**
      * @depends testGetDefaultAdapterNameReturnsConstantValue
      */
    public function testConstructSetsDefaultAdapterThatWasInitialized()
    {
        $expected = $this->getOptsArray();
        $cp = new $this->cut($expected);
        $rprop = new ReflectionProperty($this->cut, 'adapters');
        $rprop->setAccessible(true);
        $adapters = $rprop->getValue($cp);
        $this->assertArrayHasKey($cp->getDefaultAdapterName(), $adapters);
        $default = $adapters[$cp->getDefaultAdapterName() ];
        $vars = array(
            'host' => $default->getHost(),
            'user' => $default->getUser(),
        );
        foreach ($vars as $key => $value) {
            $this->assertEquals($expected[$key], $value);
        }
    }
    /**
     * @depends testConstructSetsDefaultAdapterThatWasInitialized
     */
    public function testConstructorSetsResponseObjectInAdapter()
    {
        $expected = $this->getOptsArray();
        $cp = new $this->cut($expected);
        $rprop = new ReflectionProperty($this->cut, 'adapters');
        $rprop->setAccessible(true);
        $adapters = $rprop->getValue($cp);
        $this->assertArrayHasKey($cp->getDefaultAdapterName(), $adapters);
        $default = $adapters[$cp->getDefaultAdapterName() ];
        $rObj = $default->getResponseObject();
        $this->assertInstanceOf($this->qa, $rObj);
        $this->assertEquals($default->getAdapterResponseFormatType(), $rObj->getResponseFormatType());
    }
    public function adapterData()
    {
        return array(
            array(
                'whostmgr',
                'whostmgr'
            ),
            array(
                'whm',
                'whostmgr'
            ),
            array(
                'live',
                'live'
            ),
            array(
                'livephp',
                'live'
            ),
            array(
                'Live',
                'live'
            ),
            array(
                'local',
                'live'
            ),
            array(
                'cpanel',
                'cpanel'
            ),
            array(
                'Cpanelapi',
                false
            ),
        );
    }
    /**
     * @dataProvider adapterData
     */
    public function testValidAdapter($type, $expected)
    {
        $opts = $this->getOptsArray();
        $cp = new $this->cut($opts);
        $this->assertEquals($expected, $cp->validAdapter($type));
    }
    public function adapterTypes()
    {
        return array(
            array(
                'whostmgr',
                'Cpanel_Service_Adapter_WHMapi'
            ),
            array(
                'blah',
                'Cpanel_Service_Adapter_Cpanelapi'
            ),
            array(
                'cpanel',
                'Cpanel_Service_Adapter_Cpanelapi'
            ),
            array(
                'live',
                'Cpanel_Service_Adapter_Liveapi'
            ),
        );
    }
    /**
     * @dataProvider adapterTypes
     */
    public function testProtectedSpawnAdapter($type, $expected)
    {
        $opts = $this->getOptsArray();
        $cp = new $this->cut($opts);
        $rmeth = new ReflectionMethod($cp, 'spawnAdapter');
        $rmeth->setAccessible(true);
        $this->assertInstanceOf($expected, $rmeth->invoke($cp, $type));
    }
    public function directURLInput()
    {
        return array(
            // $uri, $expectRFT, $formdata, $queryOptions
            array(
                '/xml-api/',
                'XML',
                array(
                    'foo' => 'bar'
                ),
                array(
                    'customHeader' => array(
                        'blah' => 'baz'
                    )
                )
            ),
            array(
                '/json-api/',
                'JSON',
                array(
                    'foo' => 'bar'
                ),
                array(
                    'customHeader' => array(
                        'blah' => 'baz'
                    )
                )
            ),
            array(
                '/xml/blah',
                'JSON',
                array(
                    'foo' => 'bar'
                ),
                array(
                    'customHeader' => array(
                        'blah' => 'baz'
                    )
                )
            ), //the Cpanelapi has default 'JSON' RFT
            
        );
    }
    /**
     * @dataProvider directURLInput
     */
    public function testDirectURLQuerySetsOutputFormatForRObj($uri, $RFT, $formdata, $queryOptions)
    {
        $opts = $this->getOptsArray();
        $cp = new $this->cut($opts);
        $mockAdapter = $this->getMock('Cpanel_Service_Adapter_Cpanelapi', array(
            'makeQuery'
        ));
        $mockAdapter->expects($this->once())->method('makeQuery');
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            "{$cp->getDefaultAdapterName() }" => $mockAdapter
        ));
        $r = $cp->directURLQuery($uri, $formdata, $queryOptions);
        $rObj = $mockAdapter->getResponseObject();
        $this->assertEquals($RFT, $rObj->getResponseFormatType());
    }
    /**
     * @dataProvider directURLInput
     */
    public function testDirectURLQuerySetsQueryOptionsForRObj($uri, $RFT, $formdata, $queryOptions)
    {
        $opts = $this->getOptsArray();
        $cp = new $this->cut($opts);
        $mockAdapter = $this->getMock('Cpanel_Service_Adapter_Cpanelapi', array(
            'makeQuery'
        ));
        $mockAdapter->expects($this->once())->method('makeQuery');
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            "{$cp->getDefaultAdapterName() }" => $mockAdapter
        ));
        $r = $cp->directURLQuery($uri, $formdata, $queryOptions);
        $rObj = $mockAdapter->getResponseObject();
        foreach ($queryOptions as $key => $value) {
            $stored = $rObj->query->$key;
            $this->assertNotNull($stored);
            if (is_object($stored)) {
                $stored = $stored->getAllDataRecursively();
            }
            $this->assertEquals($queryOptions[$key], $stored);
        }
    }
    /**
     * @dataProvider directURLInput
     */
    public function testDirectURLQueryPassesURLToAdapterMakeQuery($uri, $RFT, $formdata, $queryOptions)
    {
        $opts = $this->getOptsArray();
        $cp = new $this->cut($opts);
        $mockAdapter = $this->getMock('Cpanel_Service_Adapter_Cpanelapi', array(
            'makeQuery'
        ));
        $mockAdapter->expects($this->once())->method('makeQuery')->will($this->returnArgument(0));
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            "{$cp->getDefaultAdapterName() }" => $mockAdapter
        ));
        $r = $cp->directURLQuery($uri, $formdata, $queryOptions);
        $this->assertEquals($uri, $r);
    }
    /**
     * @dataProvider directURLInput
     */
    public function testDirectURLQueryPassesFormdataToAdapterMakeQuery($uri, $RFT, $formdata, $queryOptions)
    {
        $opts = $this->getOptsArray();
        $cp = new $this->cut($opts);
        $mockAdapter = $this->getMock('Cpanel_Service_Adapter_Cpanelapi', array(
            'makeQuery'
        ));
        $mockAdapter->expects($this->once())->method('makeQuery')->will($this->returnArgument(1));
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            "{$cp->getDefaultAdapterName() }" => $mockAdapter
        ));
        $r = $cp->directURLQuery($uri, $formdata, $queryOptions);
        $this->assertEquals($formdata, $r);
    }
    public function testMagicCallMethodOnAdapterWithArg0()
    {
        $opts = $this->getOptsArray();
        $cp = new $this->cut($opts);
        $expected1 = array(
            'blah' => 'baz'
        );
        $expected0 = 'functionName';
        $mockAdapter = $this->getMock('Cpanel_Service_Adapter_Cpanelapi', array(
            'xmlapi_query'
        ));
        $mockAdapter->expects($this->once())->method('xmlapi_query')->will($this->returnArgument(0));
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            "{$cp->getDefaultAdapterName() }" => $mockAdapter
        ));
        $r = $cp->xmlapi_query($expected0, $expected1);
        $this->assertEquals($expected0, $r);
    }
    public function testMagicCallMethodOnAdapterWithArg1()
    {
        $opts = $this->getOptsArray();
        $cp = new $this->cut($opts);
        $expected1 = array(
            'blah' => 'baz'
        );
        $expected0 = 'functionName';
        $mockAdapter = $this->getMock('Cpanel_Service_Adapter_Cpanelapi', array(
            'xmlapi_query'
        ));
        $mockAdapter->expects($this->once())->method('xmlapi_query')->will($this->returnArgument(1));
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            "{$cp->getDefaultAdapterName() }" => $mockAdapter
        ));
        $r = $cp->xmlapi_query($expected0, $expected1);
        $this->assertEquals($expected1, $r);
    }
    public function testApi1RequestEnforcesCheckParams()
    {
        $aservice = 'cpanel';
        $mf = array(
            'module' => 'foo',
            'function' => 'bar'
        );
        $args = array(
            'one',
            'two',
            'three'
        );
        $mockAdapter = $this->getMock('Cpanel_Service_Adapter_Cpanelapi', array(
            'api1_query'
        ));
        $mockAdapter->expects($this->any())->method('api1_query');
        $cp = $this->getCP(array(
            'checkParams'
        ), array(
            $this->getOptsArray()
        ));
        $cp->expects($this->once())->method('checkParams')->with($aservice, $mf, $args, 'api1_request', Cpanel_Service_Abstract::API1ARGS)->will($this->returnValue($this->anything()));
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            "{$cp->getDefaultAdapterName() }" => $mockAdapter
        ));
        $cp->api1_request($aservice, $mf, $args);
    }
    public function testApi1RequestInitializesAdapter()
    {
        $aservice = 'cpanel';
        $mf = array(
            'module' => 'foo',
            'function' => 'bar'
        );
        $args = array(
            'one',
            'two',
            'three'
        );
        $mockAdapter = $this->getMock('Cpanel_Service_Adapter_Cpanelapi', array(
            'api1_query'
        ));
        $mockAdapter->expects($this->any())->method('api1_query');
        $cp = $this->getCP(array(
            'checkParams',
            'initAdapter'
        ), array(
            $this->getOptsArray()
        ));
        $cp->expects($this->once())->method('checkParams')->with($aservice, $mf, $args, 'api1_request', Cpanel_Service_Abstract::API1ARGS)->will($this->returnValue($this->anything()));
        $cp->expects($this->once())->method('initAdapter')->with($mockAdapter);
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            "{$cp->getDefaultAdapterName() }" => $mockAdapter
        ));
        $cp->api1_request($aservice, $mf, $args);
    }
    /**
     * @outputBuffering disabled
     */
    public function testApi1RequestCallsRemoteAdapterApi1Query()
    {
        $opts = $this->getOptsArray();
        $aservice = 'cpanel';
        $mf = array(
            'module' => 'foo',
            'function' => 'bar'
        );
        $args = array(
            'one',
            'two',
            'three'
        );
        $userInfo = posix_getpwuid(posix_geteuid());
        $account = (array_key_exists('user', $opts)) ? $opts['user'] : $userInfo['name'];
        $mockAdapter = $this->getMock('Cpanel_Service_Adapter_Cpanelapi', array(
            'api1_query'
        ));
        $mockAdapter->expects($this->once())->method('api1_query')->with($account, $mf['module'], $mf['function'], $args);
        $cp = $this->getCP(array(
            'checkParams'
        ), array(
            $opts
        ));
        $cp->expects($this->once())->method('checkParams')->with($aservice, $mf, $args, 'api1_request', Cpanel_Service_Abstract::API1ARGS)->will($this->returnValue($this->anything()));
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            "{$cp->getDefaultAdapterName() }" => $mockAdapter
        ));
        $cp->api1_request($aservice, $mf, $args);
    }
    public function testApi1RequestWillNotInitializeLocalAdapter()
    {
        $aservice = 'cpanel';
        $mf = array(
            'module' => 'foo',
            'function' => 'bar'
        );
        $args = array(
            'one',
            'two',
            'three'
        );
        $this->setLocalEnviro();
        $adapterName = 'Cpanel_Service_Adapter_Liveapi';
        $mockAdapter = $this->getMock($adapterName, array(
            'openCpanelHandle',
            'makeQuery'
        ));
        $mockAdapter->expects($this->any())->method('openCpanelHandle');
        $mockAdapter->expects($this->any())->method('makeQuery');
        $cp = $this->getCP(array(
            'checkParams',
            'initAdapter'
        ), array(
            $this->getOptsArray()
        ));
        $cp->expects($this->once())->method('checkParams')->with($aservice, $mf, $args, 'api1_request', Cpanel_Service_Abstract::API1ARGS)->will($this->returnValue($this->anything()));
        $cp->expects($this->never())->method('initAdapter');
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            'live' => $mockAdapter
        ));
        $cp->api1_request($aservice, $mf, $args);
        $this->unsetLocalEnviro();
    }
    public function testApi1RequestCallsLocalAdapterMakeQuery()
    {
        $aservice = 'cpanel';
        $mf = array(
            'module' => 'foo',
            'function' => 'bar'
        );
        $args = array(
            'one',
            'two',
            'three'
        );
        $this->setLocalEnviro();
        $adapterName = 'Cpanel_Service_Adapter_Liveapi';
        $mockAdapter = $this->getMock($adapterName, array(
            'openCpanelHandle',
            'makeQuery'
        ));
        $mockAdapter->expects($this->any())->method('openCpanelHandle');
        $mockAdapter->expects($this->once())->method('makeQuery')->with('exec', "1", $mf['module'], $mf['function'], $args);
        $cp = $this->getCP(array(
            'checkParams',
            'initAdapter'
        ), array(
            $this->getOptsArray()
        ));
        $cp->expects($this->once())->method('checkParams')->with($aservice, $mf, $args, 'api1_request', Cpanel_Service_Abstract::API1ARGS)->will($this->returnValue($this->anything()));
        $cp->expects($this->any())->method('initAdapter');
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            'live' => $mockAdapter
        ));
        $cp->api1_request($aservice, $mf, $args);
        $this->unsetLocalEnviro();
    }
    public function testApi2RequestEnforcesCheckParams()
    {
        $aservice = 'cpanel';
        $mf = array(
            'module' => 'foo',
            'function' => 'bar'
        );
        $args = array(
            'one' => 'one',
            'two' => 'two',
            'three' => 'three'
        );
        $mockAdapter = $this->getMock('Cpanel_Service_Adapter_Cpanelapi', array(
            'api2_query'
        ));
        $mockAdapter->expects($this->any())->method('api2_query');
        $cp = $this->getCP(array(
            'checkParams'
        ), array(
            $this->getOptsArray()
        ));
        $cp->expects($this->once())->method('checkParams')->with($aservice, $mf, $args, 'api2_request', Cpanel_Service_Abstract::API2ARGS)->will($this->returnValue($this->anything()));
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            "{$cp->getDefaultAdapterName() }" => $mockAdapter
        ));
        $cp->api2_request($aservice, $mf, $args);
    }
    public function testAp21RequestInitializesAdapter()
    {
        $aservice = 'cpanel';
        $mf = array(
            'module' => 'foo',
            'function' => 'bar'
        );
        $args = array(
            'one' => 'one',
            'two' => 'two',
            'three' => 'three'
        );
        $mockAdapter = $this->getMock('Cpanel_Service_Adapter_Cpanelapi', array(
            'api2_query'
        ));
        $mockAdapter->expects($this->any())->method('api2_query');
        $cp = $this->getCP(array(
            'checkParams',
            'initAdapter'
        ), array(
            $this->getOptsArray()
        ));
        $cp->expects($this->once())->method('checkParams')->with($aservice, $mf, $args, 'api2_request', Cpanel_Service_Abstract::API2ARGS)->will($this->returnValue($this->anything()));
        $cp->expects($this->once())->method('initAdapter')->with($mockAdapter);
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            "{$cp->getDefaultAdapterName() }" => $mockAdapter
        ));
        $cp->api2_request($aservice, $mf, $args);
    }
    /**
     * @outputBuffering disabled
     */
    public function testApi2RequestCallsRemoteAdapterApi2Query()
    {
        $opts = $this->getOptsArray();
        $aservice = 'cpanel';
        $mf = array(
            'module' => 'foo',
            'function' => 'bar'
        );
        $args = array(
            'one' => 'one',
            'two' => 'two',
            'three' => 'three'
        );
        $userInfo = posix_getpwuid(posix_geteuid());
        $account = (array_key_exists('user', $opts)) ? $opts['user'] : $userInfo['name'];
        $mockAdapter = $this->getMock('Cpanel_Service_Adapter_Cpanelapi', array(
            'api2_query'
        ));
        $mockAdapter->expects($this->once())->method('api2_query')->with($account, $mf['module'], $mf['function'], $args);
        $cp = $this->getCP(array(
            'checkParams'
        ), array(
            $opts
        ));
        $cp->expects($this->once())->method('checkParams')->with($aservice, $mf, $args, 'api2_request', Cpanel_Service_Abstract::API2ARGS)->will($this->returnValue($this->anything()));
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            "{$cp->getDefaultAdapterName() }" => $mockAdapter
        ));
        $cp->api2_request($aservice, $mf, $args);
    }
    public function testApi2RequestWillNotInitializeLocalAdapter()
    {
        $aservice = 'cpanel';
        $mf = array(
            'module' => 'foo',
            'function' => 'bar'
        );
        $args = array(
            'one' => 'one',
            'two' => 'two',
            'three' => 'three'
        );
        $this->setLocalEnviro();
        $adapterName = 'Cpanel_Service_Adapter_Liveapi';
        $mockAdapter = $this->getMock($adapterName, array(
            'openCpanelHandle',
            'makeQuery'
        ));
        $mockAdapter->expects($this->any())->method('openCpanelHandle');
        $mockAdapter->expects($this->any())->method('makeQuery');
        $cp = $this->getCP(array(
            'checkParams',
            'initAdapter'
        ), array(
            $this->getOptsArray()
        ));
        $cp->expects($this->once())->method('checkParams')->with($aservice, $mf, $args, 'api2_request', Cpanel_Service_Abstract::API2ARGS)->will($this->returnValue($this->anything()));
        $cp->expects($this->never())->method('initAdapter');
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            'live' => $mockAdapter
        ));
        $cp->api2_request($aservice, $mf, $args);
        $this->unsetLocalEnviro();
    }
    public function testApi2RequestCallsLocalAdapterMakeQuery()
    {
        $aservice = 'cpanel';
        $mf = array(
            'module' => 'foo',
            'function' => 'bar'
        );
        $args = array(
            'one' => 'one',
            'two' => 'two',
            'three' => 'three'
        );
        $this->setLocalEnviro();
        $adapterName = 'Cpanel_Service_Adapter_Liveapi';
        $mockAdapter = $this->getMock($adapterName, array(
            'openCpanelHandle',
            'makeQuery'
        ));
        $mockAdapter->expects($this->any())->method('openCpanelHandle');
        $mockAdapter->expects($this->once())->method('makeQuery')->with('exec', "2", $mf['module'], $mf['function'], $args);
        $cp = $this->getCP(array(
            'checkParams',
            'initAdapter'
        ), array(
            $this->getOptsArray()
        ));
        $cp->expects($this->once())->method('checkParams')->with($aservice, $mf, $args, 'api2_request', Cpanel_Service_Abstract::API2ARGS)->will($this->returnValue($this->anything()));
        $cp->expects($this->any())->method('initAdapter');
        $rprop = new ReflectionProperty($cp, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cp, array(
            'live' => $mockAdapter
        ));
        $cp->api2_request($aservice, $mf, $args);
        $this->unsetLocalEnviro();
    }
}
