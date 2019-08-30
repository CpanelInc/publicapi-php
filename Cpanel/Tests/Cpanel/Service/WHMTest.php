<?php



/**
 * @covers Cpanel_Service_WHM
 * @author davidneimeyer
 *         
 */
class Cpanel_Service_WHMTest extends CpanelTestCase
{
    protected $cut = 'Cpanel_Service_WHM';
    protected $qa = 'Cpanel_Query_Object';
    /**
     * 
     * Enter description here ...
     * @param unknown_type       $methods  
     * @param unknown_type       $args     
     * @param unknown_type       $mockName 
     * @param unknown_type       $callConst
     * @param unknown_type       $callClone
     * @param unknown_type       $callA    
     *                                       
     * @note   This method actually returns a Mock Class
     * 
     * @return Cpanel_Service_WHM
     */
    public function getWHM($methods = array(), $args = array(), $mockName = '', $callConst = true, $callClone = true, $callA = true)
    {
        if (empty($methods)) {
            $methods = null;
        }
        $m = $this->_makeMock($this->cut, $methods, $args, $mockName, $callConst, $callClone, $callA);
        return $m;
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
    public function testCanInstantiateClean()
    {
        $whm = new $this->cut();
        $this->assertInstanceOf($this->cut, $whm);
    }
    public function testConstantAdapterDefaultIsDefined()
    {
        $classname = $this->cut;
        $whm = new $classname($this->getOptsArray());
        $this->assertTrue(defined("{$classname}::ADAPTER_DEFAULT"));
        $this->assertEquals('whostmgr', $classname::ADAPTER_DEFAULT);
    }
    /**
     * @depends testConstantAdapterDefaultIsDefined
     */
    public function testGetDefaultAdapterNameReturnsConstantValue()
    {
        $classname = $this->cut;
        $whm = new $classname($this->getOptsArray());
        $this->assertEquals($classname::ADAPTER_DEFAULT, $whm->getDefaultAdapterName());
    }
    public function testConstructorWillSetOptions()
    {
        $expected = self::getOptsArray(); //
        $whm = new $this->cut($expected);
        $vars = array(
            'host' => $whm->getOption('host'),
            'user' => $whm->getOption('user'),
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
        $whm = new $this->cut($expected);
        $vars = array(
            'host' => $whm->getOption('host'),
            'user' => $whm->getOption('user'),
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
        $whm = new $this->cut($expected);
        $rprop = new ReflectionProperty($this->cut, 'adapters');
        $rprop->setAccessible(true);
        $adapters = $rprop->getValue($whm);
        $this->assertArrayHasKey($whm->getDefaultAdapterName(), $adapters);
        $default = $adapters[$whm->getDefaultAdapterName() ];
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
        $whm = new $this->cut($expected);
        $rprop = new ReflectionProperty($this->cut, 'adapters');
        $rprop->setAccessible(true);
        $adapters = $rprop->getValue($whm);
        $this->assertArrayHasKey($whm->getDefaultAdapterName(), $adapters);
        $default = $adapters[$whm->getDefaultAdapterName() ];
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
                'cpanel',
                false
            ),
            array(
                'WHMapi1',
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
        $whm = new $this->cut($opts);
        $this->assertEquals($expected, $whm->validAdapter($type));
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
                'Cpanel_Service_Adapter_WHMapi'
            ),
        );
    }
    /**
     * @dataProvider adapterTypes
     */
    public function testProtectedSpawnAdapter($type, $expected)
    {
        $opts = $this->getOptsArray();
        $whm = new $this->cut($opts);
        $rmeth = new ReflectionMethod($whm, 'spawnAdapter');
        $rmeth->setAccessible(true);
        $this->assertInstanceOf($expected, $rmeth->invoke($whm, $type));
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
            ), //the WHMapi1 has default 'JSON' RFT
            
        );
    }
    /**
     * @dataProvider directURLInput
     */
    public function testDirectURLQuerySetsOutputFormatForRObj($uri, $RFT, $formdata, $queryOptions)
    {
        $opts = $this->getOptsArray();
        $whm = new $this->cut($opts);
        $mockAdapter = $this->_makeMock('Cpanel_Service_Adapter_WHMapi', array(
            'makeQuery'
        ));
        $mockAdapter->expects($this->once())->method('makeQuery');
        $rprop = new ReflectionProperty($whm, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($whm, array(
            "{$whm->getDefaultAdapterName() }" => $mockAdapter
        ));
        $r = $whm->directURLQuery($uri, $formdata, $queryOptions);
        $rObj = $mockAdapter->getResponseObject();
        $this->assertEquals($RFT, $rObj->getResponseFormatType());
    }
    /**
     * @dataProvider directURLInput
     */
    public function testDirectURLQuerySetsQueryOptionsForRObj($uri, $RFT, $formdata, $queryOptions)
    {
        $opts = $this->getOptsArray();
        $whm = new $this->cut($opts);
        $mockAdapter = $this->_makeMock('Cpanel_Service_Adapter_WHMapi', array(
            'makeQuery'
        ));
        $mockAdapter->expects($this->once())->method('makeQuery');
        $rprop = new ReflectionProperty($whm, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($whm, array(
            "{$whm->getDefaultAdapterName() }" => $mockAdapter
        ));
        $r = $whm->directURLQuery($uri, $formdata, $queryOptions);
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
        $whm = new $this->cut($opts);
        $mockAdapter = $this->_makeMock('Cpanel_Service_Adapter_WHMapi', array(
            'makeQuery'
        ));
        $mockAdapter->expects($this->once())->method('makeQuery')->will($this->returnArgument(0));
        $rprop = new ReflectionProperty($whm, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($whm, array(
            "{$whm->getDefaultAdapterName() }" => $mockAdapter
        ));
        $r = $whm->directURLQuery($uri, $formdata, $queryOptions);
        $this->assertEquals($uri, $r);
    }
    /**
     * @dataProvider directURLInput
     */
    public function testDirectURLQueryPassesFormdataToAdapterMakeQuery($uri, $RFT, $formdata, $queryOptions)
    {
        $opts = $this->getOptsArray();
        $whm = new $this->cut($opts);
        $mockAdapter = $this->_makeMock('Cpanel_Service_Adapter_WHMapi', array(
            'makeQuery'
        ));
        $mockAdapter->expects($this->once())->method('makeQuery')->will($this->returnArgument(1));
        $rprop = new ReflectionProperty($whm, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($whm, array(
            "{$whm->getDefaultAdapterName() }" => $mockAdapter
        ));
        $r = $whm->directURLQuery($uri, $formdata, $queryOptions);
        $this->assertEquals($formdata, $r);
    }
    public function testMagicCallMethodOnAdapterWithArg0()
    {
        $opts = $this->getOptsArray();
        $whm = new $this->cut($opts);
        $expected1 = array(
            'blah' => 'baz'
        );
        $expected0 = 'functionName';
        $mockAdapter = $this->_makeMock('Cpanel_Service_Adapter_WHMapi', array(
            'xmlapi_query'
        ));
        $mockAdapter->expects($this->once())->method('xmlapi_query')->will($this->returnArgument(0));
        $rprop = new ReflectionProperty($whm, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($whm, array(
            "{$whm->getDefaultAdapterName() }" => $mockAdapter
        ));
        $r = $whm->xmlapi_query($expected0, $expected1);
        $this->assertEquals($expected0, $r);
    }
    public function testMagicCallMethodOnAdapterWithArg1()
    {
        $opts = $this->getOptsArray();
        $whm = new $this->cut($opts);
        $expected1 = array(
            'blah' => 'baz'
        );
        $expected0 = 'functionName';
        $mockAdapter = $this->_makeMock('Cpanel_Service_Adapter_WHMapi', array(
            'xmlapi_query'
        ));
        $mockAdapter->expects($this->once())->method('xmlapi_query')->will($this->returnArgument(1));
        $rprop = new ReflectionProperty($whm, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($whm, array(
            "{$whm->getDefaultAdapterName() }" => $mockAdapter
        ));
        $r = $whm->xmlapi_query($expected0, $expected1);
        $this->assertEquals($expected1, $r);
    }
}
