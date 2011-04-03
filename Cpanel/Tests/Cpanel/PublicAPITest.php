<?php
require_once 'PHPUnit/Autoload.php';
/**
 * Basic test case for the Cpanel class
 * @author davidneimeyer
 * @covers Cpanel_PublicAPI
 */
class Cpanel_PublicAPITest extends PHPUnit_Framework_TestCase
{
    /**
     * Test that 'new Cpanel()' will throw an exception
     * @expectedException Exception
     * @covers            Cpanel_PublicAPI::__construct
     */
    public function testCanNotInstanciateDirectly()
    {
        $cp = new Cpanel_PublicAPI();
    }
    /**
     * Test that getInstance can be invoked without parameters
     */
    public function testGetInstanceWithoutParam()
    {
        $cp = Cpanel_PublicAPI::getInstance();
        $this->assertInternalType('object', $cp);
        return $cp;
    }
    public static $simpleArray = array(
        'a' => 1,
        'b' => 2,
        'c' => 3,
    );
    public static function getSampleWHMConfig()
    {
        return array(
            'whm' => array(
                'myserver1' => array(
                    'config' => array( //namespacing into config is preferred
                        'host' => '10.1.4.191',
                        'user' => 'root',
                        'password' => 'cp1'
                    ),
                ),
                'myserver2' => array(
                    'host' => '10.1.4.102',
                    'user' => 'root',
                    'password' => 'cp1'
                ),
            ),
        );
    }
    public static function getSampleConfig()
    {
        $pkgOpts = array(
            //'registryClass'   => 'Zend/Registry.php',
            //'registryClass'   => 'disabled',
            
        );
        $configOpts = array(
            'username' => 'root',
            'password' => 'cp1',
        );
        $services = self::getSampleWHMConfig();
        $masterConfig = array(
            'package' => $pkgOpts,
            'config' => $configOpts,
            'service' => $services,
        );
        return $masterConfig;
    }
    /**
     * Verify an instantance can be reset
     * @depends testGetInstanceWithoutParam
     */
    public function testCanResetInstance()
    {
        $cp = Cpanel_PublicAPI::getInstance();
        $rcpProp = new ReflectionProperty('Cpanel_PublicAPI', 'dataContainer');
        $rcpProp->setAccessible(true);
        $rcpProp->setValue($cp, 'foo');
        Cpanel_PublicAPI::resetInstance();
        $refreshed = Cpanel_PublicAPI::getInstance();
        $this->assertNotEquals('foo', $rcpProp->getValue($refreshed));
    }
    /**
     * Verify clone will throw exception
     * @expectedException Exception
     */
    public function testCloneThrowsException()
    {
        $cp = Cpanel_PublicAPI::getInstance();
        $no = clone $cp;
    }
    /**
     * Verify the signature of the clone method is final
     */
    public function testCloneSignatureIsFinal()
    {
        $rcpCloneMethod = new ReflectionMethod('Cpanel_PublicAPI', '__clone');
        $this->assertTrue($rcpCloneMethod->isFinal());
    }
    /**
     * Test that getInstance can be invoked with an array as param 0
     * @depends testCanResetInstance
     */
    public function testGetInstanceWithArray()
    {
        Cpanel_PublicAPI::resetInstance();
        $cp = Cpanel_PublicAPI::getInstance(self::$simpleArray);
        $this->assertInternalType('object', $cp);
        return $cp;
    }
    /**
     * Test that getInstance will return Cpanel obj
     * 
     * @depends testGetInstanceWithoutParam
     */
    public function testGetInstanceIsCpanelObject($fixture)
    {
        $this->assertInstanceOf('Cpanel_PublicAPI', $fixture);
    }
    /**
     * Verify that Cpanel class extends Cpanel_Core_Object
     * 
     * @depends testGetInstanceWithoutParam
     */
    public function testCpanelExtendsCpanelObject($fixture)
    {
        $this->assertInstanceOf('Cpanel_Core_Object', $fixture);
    }
    /**
     * Helper for returning a special mock object of Cpanel that can be
     * instantiated directly
     * 
     * @param array $methods The methods to stub
     * @param array $reveal  Array of property names and their values to bless 
     *                       into the mock object
     */
    public function mockCpanel($methods, $reveal = array())
    {
        // optional reveal array lets you expose more properties than
        // the basic necessary for direct instantiation
        $reveal = (is_array($reveal)) ? $reveal : array();
        $reveal['_canInstantiate'] = true;
        //setup a mock; don't call construct when creating mock
        $stub = $this->getMockBuilder('Cpanel_PublicAPI')->disableOriginalConstructor()->setMethods($methods)->getMock();
        foreach ($reveal as $key => $value) {
            $rprop = new ReflectionProperty('Cpanel_PublicAPI', $key);
            $rprop->setAccessible(true);
            $rprop->setValue($stub, $value);
        }
        return $stub;
    }
    /**
     * Verify default behavior for Cpanel is to create a registry object
     */
    public function testDefaultCreatesRegistry()
    {
        Cpanel_PublicAPI::resetInstance();
        $rprop = new ReflectionProperty('Cpanel_PublicAPI', '_registry');
        $rprop->setAccessible(true);
        //verify that the class defines a 'blank' value
        $this->assertEmpty($rprop->getValue(new ReflectionClass('Cpanel_PublicAPI')));
        //verify instantiate does the minimum, assigns an object to $_registry
        $cp = Cpanel_PublicAPI::getInstance();
        $this->assertInternalType('object', $rprop->getValue($cp));
        return $cp;
    }
    /**
     * Verify getRegistry returns an object that implement certain accessors
     * @depends testDefaultCreatesRegistry
     */
    public function testGetRegistry($fixture)
    {
        $rmeth = new ReflectionMethod('Cpanel_PublicAPI', 'getRegistry');
        $rmeth->setAccessible(true);
        $obj = $rmeth->invoke($fixture);
        $this->assertInternalType('object', $obj);
    }
    /**
     * Verify that registerRegistry is called during instantiation
     * @covers Cpanel_PublicAPI::registerRegistry
     */
    public function testRegisterRegistry()
    {
        $stub = $this->mockCpanel(array(
            'registerRegistry'
        ));
        $stub->expects($this->once())->method('registerRegistry');
        $stub->__construct();
    }
    /**
     * Verify getServiceConfig returns something that works in a foreach loop
     * @depends testGetInstanceWithoutParam
     */
    public function testGetServiceConfigReturnsSomethingTraversable()
    {
        $config = self::getSampleConfig();
        Cpanel_PublicAPI::resetInstance();
        $cp = Cpanel_PublicAPI::getInstance();
        $result = $cp->getServiceConfig('whm', $config);
        $isCountable = ((is_array($result)) || ((is_object($result)) && ($result instanceof Traversable))) ? true : false;
        $this->assertTrue($isCountable);
    }
    /**
     * Verify that if service type is not defined in a config, the base 'service'
     * namespace is returned
     * 
     * Theory is that there may be a 'service.config' that we could get generic
     * key/values from
     * 
     * @depends testGetInstanceWithoutParam
     */
    public function testGetServiceConfigReturnsServicesNamespace()
    {
        $config = self::getSampleConfig();
        Cpanel_PublicAPI::resetInstance();
        $cp = Cpanel_PublicAPI::getInstance();
        $result = $cp->getServiceConfig('--fake--', $config);
        foreach ($result as $key => $value) {
            $this->assertArrayHasKey($key, $config['service']);
        }
    }
    /**
     * Verify that a service type can be extracted from config
     * @depends testGetInstanceWithoutParam
     */
    public function testGetServiceConfigReturnsSpecificService()
    {
        $config = self::getSampleConfig();
        Cpanel_PublicAPI::resetInstance();
        $cp = Cpanel_PublicAPI::getInstance();
        $result = $cp->getServiceConfig('whm', $config);
        foreach ($result as $key => $value) {
            $this->assertArrayHasKey($key, $config['service']['whm']);
        }
    }
    /**
     * Verify getNamedConfig will return empty array when passed an empty config
     * @depends testGetInstanceWithoutParam
     */
    public function testGetNamedConfigReturnsEmptyArrayIfPassedEmptyArray()
    {
        Cpanel_PublicAPI::resetInstance();
        $cp = Cpanel_PublicAPI::getInstance();
        $result = $cp->getNamedConfig('myserver1', array());
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }
    /**
     * Verify getNamedConfig will return empty array when passed a name not present
     * @depends testGetInstanceWithoutParam
     */
    public function testGetNamedConfigReturnsEmptyArrayIfPassedNameNotInConfig()
    {
        $config = self::getSampleConfig();
        $cpObj = new Cpanel_Core_Object($config['service']['whm']);
        Cpanel_PublicAPI::resetInstance();
        $cp = Cpanel_PublicAPI::getInstance();
        $result = $cp->getNamedConfig('--fake--', $cpObj);
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }
    /**
     * Verify getNamedConfig will return namespace's 'config' subspace
     * @depends testGetInstanceWithoutParam
     */
    public function testGetNamedConfigReturnsNamedConfig()
    {
        $config = self::getSampleConfig();
        $cpObj = new Cpanel_Core_Object($config['service']['whm']);
        Cpanel_PublicAPI::resetInstance();
        $cp = Cpanel_PublicAPI::getInstance();
        $name = 'myserver1';
        $result = $cp->getNamedConfig($name, $cpObj);
        foreach ($result as $key => $value) {
            $this->assertArrayHasKey($key, $config['service']['whm'][$name]['config']);
        }
    }
    /**
     * Verify getNamedConfig will return namespace base if 'config' subspace is
     * not present
     * 
     * @depends testGetInstanceWithoutParam
     */
    public function testGetNamedConfigReturnsNamedBase()
    {
        $config = self::getSampleConfig();
        $cpObj = new Cpanel_Core_Object($config['service']['whm']);
        Cpanel_PublicAPI::resetInstance();
        $cp = Cpanel_PublicAPI::getInstance();
        $name = 'myserver2';
        $result = $cp->getNamedConfig($name, $cpObj);
        foreach ($result as $key => $value) {
            $this->assertArrayHasKey($key, $config['service']['whm'][$name]);
        }
    }
    /**
     * mergeConfig should recursive merge array2 into array1
     * (array1 values are overwritten where keys are exactly the same) 
     * @depends testGetInstanceWithoutParam
     */
    public function testMergeConfig()
    {
        $arr1 = array(
            'a' => array(
                'aa' => array(
                    'aaa' => 1,
                    'ccc' => 3
                )
            ),
            'b' => 2
        );
        $arr2 = array(
            'a' => array(
                'aa' => array(
                    'aaa' => 11
                )
            ),
            'b' => 22
        );
        $expected = array(
            'a' => array(
                'aa' => array(
                    'aaa' => 11,
                    'ccc' => 3
                )
            ),
            'b' => 22
        );
        Cpanel_PublicAPI::resetInstance();
        $cp = Cpanel_PublicAPI::getInstance();
        $actual = $cp->mergeConfigs($arr1, $arr2);
        $this->assertEquals($expected, $actual);
    }
    /**
     * Get an aggregated config from the 4 sources: user provided named
     * 'config' subspace, user provided service base, stored named
     * 'config' subspace, and stored default service base
     * 
     * @depends testMergeConfig
     */
    public function testGetAggregateConfig()
    {
        $user = array(
            'whm' => array(
                'config' => array(
                    'hom' => 'sar'
                ),
                'mywhm' => array(
                    'config' => array(
                        'flotsam' => 'jetsam'
                    )
                )
            )
        );
        $namedconf = array(
            'service' => array(
                'whm' => array(
                    'config' => array(
                        'host' => 'baseconfig'
                    ),
                    'mywhm' => array(
                        'config' => array(
                            'foo' => 'bar'
                        )
                    )
                )
            )
        );
        $expected = array(
            'host' => 'baseconfig',
            'foo' => 'bar',
            'flotsam' => 'jetsam',
            'hom' => 'sar'
        );
        Cpanel_PublicAPI::resetInstance();
        $cp = Cpanel_PublicAPI::getInstance($namedconf);
        $rmeth = new ReflectionMethod('Cpanel_PublicAPI', '_getAggregateConfig');
        $rmeth->setAccessible(true);
        $actual = $rmeth->invoke($cp, 'whm', $user, 'mywhm');
        $this->assertEquals($expected, $actual);
    }
    /**
     * Factory should throw exception when invoked with 'blank' type, arg-0
     * @expectedException Exception
     */
    public function testFactoryThrowsExceptionWithArg0()
    {
        Cpanel_PublicAPI::resetInstance();
        Cpanel_PublicAPI::factory('', array(), '');
    }
    /**
     * Verify 'WHM' object is returned
     */
    public function testFactoryCanReturnWHM()
    {
        Cpanel_PublicAPI::resetInstance();
        $objtype = 'WHM';
        $optsArray = $this->getSampleConfig();
        $name = 'myserver1';
        $obj = Cpanel_PublicAPI::factory($objtype, $optsArray, $name);
        $this->assertInstanceOf("Cpanel_Service_{$objtype}", $obj);
    }
    /**
     * Verify factory will return valid service is simple use case
     * @depends testFactoryCanReturnWHM
     */
    public function testFactoryCanReturnWHMFromSimpleConfig()
    {
        Cpanel_PublicAPI::resetInstance();
        $arr = array(
            'service' => array(
                'whm' => array(
                    'config' => array(
                        'host' => '1.1.1.1'
                    )
                )
            )
        );
        $cp = Cpanel_PublicAPI::getInstance($arr);
        $obj = $cp->factory('WHM');
        $this->assertEquals('1.1.1.1', $obj->getOption('host'));
    }
    /**
     * @depends         testGetRegistry
     * @outputBuffering disabled
     */
    public function testFactoryStoresObjectInRegistry()
    {
        //get a mock Cpanel package
        $mock = $this->mockCpanel(array(
            '__clone'
        ));
        $mock->resetInstance();
        $cp = $mock->__construct();
        //some vars
        $objtype = 'WHM';
        $optsArray = $this->getSampleConfig();
        $name = 'myserver1';
        $storedServiceName = strtolower($objtype) . "_{$name}";
        // verify reg is doesn't have our named service
        $rmeth = new ReflectionMethod('Cpanel_PublicAPI', 'getRegistry');
        $rmeth->setAccessible(true);
        $reg = $rmeth->invoke($cp);
        $reg = $reg->getArrayCopy();
        $this->assertArrayNotHasKey($storedServiceName, $reg);
        //run factory, should trigger registry storage
        $service = $cp->factory($objtype, $optsArray, $name);
        //verify that named service is now register
        $rmeth = new ReflectionMethod('Cpanel_PublicAPI', 'getRegistry');
        $rmeth->setAccessible(true);
        $reg = $rmeth->invoke($cp);
        $reg = $reg->getArrayCopy();
        $this->assertArrayHasKey($storedServiceName, $reg);
        //verify that stored service is identical to one returned by factory
        $storedID = spl_object_hash($reg[$storedServiceName]);
        $returnedID = spl_object_hash($service);
        $this->assertEquals($storedID, $returnedID);
    }
    /**
     * Test magic factory method
     * @depends testFactoryCanReturnWHM
     */
    public function testCallerReturnsRequestedObj()
    {
        $stub = $this->mockCpanel(array(
            '__clone'
        ));
        $arr = array(
            'name' => 'myserver1',
            'service' => self::getSampleWHMConfig()
        );
        $service = $stub->getWHM($arr);
        $this->assertInstanceOf('Cpanel_Service_WHM', $service);
    }
    /**
     * Verify exception is throw when no method is passed for indirect 
     * invocation
     * 
     * @depends           testFactoryCanReturnWHMFromSimpleConfig
     * @expectedException Exception
     * @outputBuffering   disabled
     */
    public function testCallerThrowExceptionWhenNoArgsOnDispatchedApiRequest()
    {
        $arr = array(
            'service' => array(
                'whm' => array(
                    'config' => array(
                        'host' => '1.1.1.1'
                    )
                )
            )
        );
        Cpanel_PublicAPI::resetInstance();
        $cp = Cpanel_PublicAPI::getInstance($arr);
        $response = $cp->whm_api();
    }
}
?>
