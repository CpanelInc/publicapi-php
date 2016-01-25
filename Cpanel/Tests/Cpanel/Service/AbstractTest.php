<?php
require 'Cpanel/Util/Autoload.php';

class concreteCoreService extends Cpanel_Service_Abstract
{
    public function getDefaultAdapterName()
    {
    }
    public function validAdapter($type)
    {
    }
    public function spawnAdapter($adapterType)
    {
    }
}
class concreteCoreRemoteQuery extends Cpanel_Query_Http_Abstract
{
    public function getAdapterResponseFormatType()
    {
    }
    public function setAdapterResponseFormatType($type)
    {
    }
}
class mockCoreException extends Exception
{
}
/**
 * @covers Cpanel_Service_Abstract
 * @author davidneimeyer
 *         
 */
class Cpanel_Service_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Cpanel_Service_Abstract
     */
    protected $cs;
    protected $cut = 'concreteCoreService';
    protected $real_cut = 'Cpanel_Service_Abstract';
    protected $qa = 'Cpanel_Query_Object';
    /**
     * 
     * @param unknown_type            $methods  
     * @param unknown_type            $args     
     * @param unknown_type            $mockName 
     * @param unknown_type            $callConst
     * @param unknown_type            $callClone
     * @param unknown_type            $callA    
     *                                            
     * @note   This method actually returns a Mock Class
     * 
     * @return Cpanel_Service_Abstract
     */
    public function getCS($methods = array(), $args = array(), $mockName = '', $callConst = true, $callClone = true, $callA = true)
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

    public function tearDown()
    {
        self::restoreHash();
    }

    public function createHash()
    {
        $userInfo = posix_getpwuid(posix_geteuid());
        $filename = $userInfo['dir'] . '/.accesshash';
        $hash = "foo\nbar\n";
        $fh = fopen($filename, 'w+');
        fwrite($fh, $hash);
        fclose($fh);
        return $hash;
    }
    public function removeHash()
    {
        $userInfo = posix_getpwuid(posix_geteuid());
        $filename = $userInfo['dir'] . '/.accesshash';
        $filenameBck = $filename."_bck";

        if (file_exists($filename)) {
            if(!rename($filename, $filenameBck)) {
                self::fail("Can't create backup file of $filename");
                exit();
            }
        }
    }

    public function restoreHash()
    {
        $userInfo = posix_getpwuid(posix_geteuid());
        $filename = $userInfo['dir'] . '/.accesshash';
        $filenameBck = $filename."_bck";

        if (file_exists($filenameBck)) {
            if(!rename($filenameBck, $filename)) {
                self::fail("Can't restore file $filename. Please restore it manually from $filenameBck");
                exit();
            }
        }
    }

    public function setEnvironmentVars($password = false, $server = 'cpsrvd')
    {
        if ($password) {
            putenv('REMOTE_PASSWORD=' . $password);
        } elseif (is_null($password)) {
            //set to blank
            putenv('REMOTE_PASSWORD=');
        } elseif ($password === false) {
            putenv('REMOTE_PASSWORD=__HIDDEN__');
        }
        putenv('SERVER_SOFTWARE=' . $server);
    }
    public function removeEnvironmentVars()
    {
        putenv('REMOTE_PASSWORD');
        putenv('SERVER_SOFTWARE');
    }
    public function setLocal()
    {
        $filename = '/tmp/publicapi.test.sock';
        putenv('CPANEL_PHPCONNECT_SOCKET=' . $filename);
        touch($filename);
    }
    public function unsetLocal()
    {
        $filename = '/tmp/publicapi.test.sock';
        putenv('CPANEL_PHPCONNECT_SOCKET');
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
    public function testHasAbstractMethods()
    {
        $expected = array(
            'getDefaultAdapterName',
            'validAdapter',
            'spawnAdapter',
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
     * @expectedException Exception
     * @depends           testHasAbstractMethods
     */
    public function testConstructThrowsOnBadInput()
    {
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($this->cut, '__construct');
        $rmeth->invoke($cs, 'fakestring');
    }
    public function classConstants()
    {
        return array(
            array(
                'API1ARGS',
                'ordinal'
            ),
            array(
                'API2ARGS',
                'associative'
            ),
            array(
                'ADAPTER_CPANEL',
                'cpanel'
            ),
            array(
                'ADAPTER_WHM',
                'whostmgr'
            ),
            array(
                'ADAPTER_LIVE',
                'live'
            ),
        );
    }
    /**
     * @dataProvider classConstants
     *               
     */
    public function testClassHasConstants($name, $value)
    {
        $rclass = new ReflectionClass($this->real_cut);
        $actual = $rclass->getConstants();
        $this->assertArrayHasKey($name, $actual);
        $this->assertEquals($value, $actual[$name]);
    }
    /**
     *
     */
    public function testInstantiationSetsALogger()
    {
        $cs = new $this->cut();
        $this->assertInstanceOf('Cpanel_Listner_Subject_Abstract', $cs->listner);
    }
    /**
     * @expectedException Exception
     */
    public function testDisableAdapterThrowOnBadType()
    {
        $cs = $this->getCS(array(
            'validAdapter'
        ));
        $cs->expects($this->once())->method('validAdapter')->will($this->returnValue(false));
        $cs->disableAdapter('cpanel');
    }
    public function testDisableAdapterStoresOnValidType()
    {
        $cs = $this->getCS(array(
            'validAdapter'
        ));
        $cs->expects($this->once())->method('validAdapter')->will($this->returnValue('foo'));
        $cs->disableAdapter('cpanel');
        $rprop = new ReflectionProperty($cs, 'disabledAdapters');
        $rprop->setAccessible(true);
        $da = $rprop->getValue($cs);
        $this->assertTrue(in_array('foo', $da));
    }
    /**
     * @expectedException Exception
     */
    public function testEnableAdapterThrowOnBadType()
    {
        $cs = $this->getCS(array(
            'validAdapter'
        ));
        $cs->expects($this->once())->method('validAdapter')->will($this->returnValue(false));
        $cs->enableAdapter('cpanel');
    }
    public function testEnableAdapterRemovesOnValidType()
    {
        $cs = $this->getCS(array(
            'validAdapter'
        ));
        $cs->expects($this->once())->method('validAdapter')->will($this->returnValue('foo'));
        $rprop = new ReflectionProperty($cs, 'disabledAdapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cs, array(
            'foo'
        ));
        $cs->enableAdapter('cpanel');
        $rprop = new ReflectionProperty($cs, 'disabledAdapters');
        $rprop->setAccessible(true);
        $da = $rprop->getValue($cs);
        $this->assertFalse(in_array('foo', $da));
    }
    public function testPrivategetEUIDAuthReturnsArray()
    {
        $this->removeHash();
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, '_getEUIDAuth');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs);
        $this->assertInternalType('array', $r);
    }
    /**
     * @depends testPrivategetEUIDAuthReturnsArray
     */
    public function testPrivategetEUIDAuthReturnArrayWithParticularKeys()
    {
        $this->removeHash();
        $expected = array(
            'hash' => '',
            'password' => ''
        );
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, '_getEUIDAuth');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs);
        $this->assertEquals($expected, $r);
    }
    /**
     * @depends testPrivategetEUIDAuthReturnsArray
     */
    public function testPrivategetEUIDAuthReturnArrayWithHashWhenAvailable()
    {
        $this->removeHash();
        $hash = $this->createHash();
        $expected = array(
            'hash' => $hash,
            'password' => ''
        );
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, '_getEUIDAuth');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs);
        $this->assertEquals($expected, $r);

    }
    /**
     * @depends testPrivategetEUIDAuthReturnsArray
     */
    public function testPrivategetEUIDAuthReturnArrayWithPasswordWhenAvailable()
    {
        $this->removeHash();
        $this->setEnvironmentVars('foo');
        $expected = array(
            'hash' => '',
            'password' => 'foo'
        );
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, '_getEUIDAuth');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs);
        $this->assertEquals($expected, $r);
        $this->removeEnvironmentVars();
    }
    /**
     * @depends testPrivategetEUIDAuthReturnsArray
     */
    public function testPrivategetEUIDAuthReturnArrayWithoutPasswordWhenNoPassword()
    {
        $this->removeHash();
        $this->setEnvironmentVars(null);
        $expected = array(
            'hash' => '',
            'password' => ''
        );
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, '_getEUIDAuth');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs);
        $this->assertEquals($expected, $r);
        $this->removeEnvironmentVars();
    }
    /**
     * @depends testPrivategetEUIDAuthReturnsArray
     */
    public function testPrivategetEUIDAuthReturnArrayWithoutPasswordWhenPasswordHidden()
    {
        $this->removeHash();
        $this->setEnvironmentVars(false);
        $expected = array(
            'hash' => '',
            'password' => ''
        );
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, '_getEUIDAuth');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs);
        $this->assertEquals($expected, $r);
        $this->removeEnvironmentVars();
    }
    /**
     * @depends testPrivategetEUIDAuthReturnsArray
     */
    public function testPrivategetEUIDAuthReturnArrayWithoutPasswordWhenNotCpsrvd()
    {
        $this->removeHash();
        $this->setEnvironmentVars('foo', 'webmail');
        $expected = array(
            'hash' => '',
            'password' => ''
        );
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, '_getEUIDAuth');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs);
        $this->assertEquals($expected, $r);
        $this->removeEnvironmentVars();
    }
    /**
     * @depends testPrivategetEUIDAuthReturnsArray
     */
    public function testPrivateGetEnvironmentContextAlwaysReturnsArrayWithKeys()
    {
        $this->removeHash();
        $this->removeEnvironmentVars();
        $expected = array(
            'hash' => '',
            'password' => '',
            'user' => '',
            'host' => ''
        );
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, '_getEnvironmentContext');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs);
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $r);
        }
    }
    /**
     * @depends testPrivateGetEnvironmentContextAlwaysReturnsArrayWithKeys
     */
    public function testPrivateGetEnvironmentContextSetsHostByDefault()
    {
        $this->removeHash();
        $this->removeEnvironmentVars();
        $host = '127.0.0.1';
        $expected = array(
            'hash' => '',
            'password' => '',
            'user' => '',
            'host' => ''
        );
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, '_getEnvironmentContext');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs);
        $this->assertEquals($host, $r['host']);
    }
    /**
     * @depends testPrivateGetEnvironmentContextSetsHostByDefault
     */
    public function testPrivateGetEnvironmentContextHonorsExistingHost()
    {
        $this->removeHash();
        $this->removeEnvironmentVars();
        $host = '127.0.0.3';
        $expected = array(
            'hash' => '',
            'password' => '',
            'user' => '',
            'host' => $host
        );
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, '_getEnvironmentContext');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs, $expected);
        $this->assertEquals($host, $r['host']);
    }
    /**
     * @depends testPrivateGetEnvironmentContextSetsHostByDefault
     */
    public function testPrivateGetEnvironmentContextSetsUserByDefault()
    {
        $this->removeHash();
        $this->removeEnvironmentVars();
        $userInfo = posix_getpwuid(posix_geteuid());
        $user = $userInfo['name'];
        $expected = array(
            'hash' => '',
            'password' => '',
            'user' => '',
            'host' => ''
        );
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, '_getEnvironmentContext');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs, $expected);
        $this->assertEquals($user, $r['user']);
    }
    /**
     * @depends testPrivateGetEnvironmentContextSetsHostByDefault
     */
    public function testPrivateGetEnvironmentContextHonorsExistingUser()
    {
        $this->removeHash();
        $this->removeEnvironmentVars();
        $user = 'foo';
        $expected = array(
            'hash' => '',
            'password' => '',
            'user' => $user,
            'host' => ''
        );
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, '_getEnvironmentContext');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs, $expected);
        $this->assertEquals($user, $r['user']);
    }
    /**
     * @depends testPrivateGetEnvironmentContextSetsHostByDefault
     *          
     */
    public function testPrivateGetEnvironmentContextSetsHashByDefaultIfAvailable()
    {
        $this->removeHash();
        $this->removeEnvironmentVars();
        $hash = $this->createHash();
        $expected = array(
            'hash' => '',
            'password' => '',
            'user' => '',
            'host' => ''
        );
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, '_getEnvironmentContext');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs, $expected);
        $this->assertEquals($hash, $r['hash']);
    }
    /**
     * @depends testPrivateGetEnvironmentContextSetsHostByDefault
     *          
     */
    public function testPrivateGetEnvironmentContextSetsPasswordByDefaultIfAvailable()
    {
        $this->removeHash();
        $this->removeEnvironmentVars();
        $p = 'foo';
        $this->setEnvironmentVars($p);
        $expected = array(
            'hash' => '',
            'password' => '',
            'user' => '',
            'host' => ''
        );
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, '_getEnvironmentContext');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs, $expected);
        $this->assertEquals($p, $r['password']);
        $this->assertEmpty($r['hash']);
    }
    /**
     * 
     */
    public function testInitAdapterRequiresRemoteQueryAsArg()
    {
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, 'initAdapter');
        $rparams = $rmeth->getParameters();
        $actual = $rparams[0]->getClass()->getName();
        $this->assertEquals('Cpanel_Query_Http_Abstract', $actual);
    }
    /**
     * 
     */
    public function testInitAdapterAttemptsToGetVariousEnviromentVarsInternalBeforeSetting()
    {
        $hash = 'blah';
        $password = 'blurg';
        $user = 'foo';
        $host = 'bar';
        $port = '2095';
        $protocol = 'http';
        $expected = array(
            'hash' => $hash,
            'password' => $password,
            'user' => $user,
            'host' => $host,
            'port' => $port,
            'protocol' => $protocol,
        );
        $cs = $this->getCS(array(
            'getOption'
        ), array(
            $expected
        ));
        $cs->expects($this->at(0))->method('getOption')->with('host')->will($this->returnValue($host));
        $cs->expects($this->at(1))->method('getOption')->with('user')->will($this->returnValue($user));
        $cs->expects($this->at(2))->method('getOption')->with('hash')->will($this->returnValue($hash));
        $cs->expects($this->at(3))->method('getOption')->with('port')->will($this->returnValue($port));
        $cs->expects($this->at(4))->method('getOption')->with('protocol')->will($this->returnValue($protocol));
        $rmeth = new ReflectionMethod($cs, 'initAdapter');
        $rmeth->setAccessible(true);
        $rmeth->invoke($cs, new concreteCoreRemoteQuery());
    }
    /**
     * @depends testInitAdapterAttemptsToGetVariousEnviromentVarsInternalBeforeSetting
     */
    public function testInitAdapterAttemptsToGetVariousEnviromentVarsInternalBeforeSettingCheckingPasswordAfterHash()
    {
        $hash = '';
        $password = 'blurg';
        $user = 'foo';
        $host = 'bar';
        $port = '2095';
        $protocol = 'http';
        $expected = array(
            'hash' => $hash,
            'password' => $password,
            'user' => $user,
            'host' => $host,
            'port' => $port,
            'protocol' => $protocol,
        );
        $cs = $this->getCS(array(
            'getOption'
        ), array(
            $expected
        ));
        $cs->expects($this->at(0))->method('getOption')->with('host')->will($this->returnValue($host));
        $cs->expects($this->at(1))->method('getOption')->with('user')->will($this->returnValue($user));
        $cs->expects($this->at(2))->method('getOption')->with('hash')->will($this->returnValue($hash));
        $cs->expects($this->at(3))->method('getOption')->with('password')->will($this->returnValue($password));
        $cs->expects($this->at(4))->method('getOption')->with('port')->will($this->returnValue($port));
        $cs->expects($this->at(5))->method('getOption')->with('protocol')->will($this->returnValue($protocol));
        $rmeth = new ReflectionMethod($cs, 'initAdapter');
        $rmeth->setAccessible(true);
        $rmeth->invoke($cs, new concreteCoreRemoteQuery());
    }
    /**
     * @depends testInitAdapterAttemptsToGetVariousEnviromentVarsInternalBeforeSetting
     */
    public function testInitAdapterAttemptsToUsePrivateGetEnvironmentContext()
    {
        $adapter = new concreteCoreRemoteQuery();
        $hash = '';
        $password = 'blurg';
        $user = '';
        $host = 'bar';
        $port = '2095';
        $protocol = 'http';
        $userInfo = posix_getpwuid(posix_geteuid());
        $expectedUser = $userInfo['name'];
        $expected = array(
            'hash' => $hash,
            'password' => $password,
            'user' => $user,
            'host' => $host,
            'port' => $port,
            'protocol' => $protocol,
        );
        $cs = $this->getCS(array(
            'getOption'
        ), array(
            $expected
        ));
        $cs->expects($this->at(6))->method('getOption')->with('disableEnvironmentContext');
        $rmeth = new ReflectionMethod($cs, 'initAdapter');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs, $adapter);
        $actual = $r->getUser();
        $this->assertEquals($expectedUser, $actual);
    }
    /**
     * @depends testInitAdapterAttemptsToGetVariousEnviromentVarsInternalBeforeSetting
     */
    public function testInitAdapterWillNotToUsePrivateGetEnvironmentContextWhenDisabled()
    {
        $adapter = new concreteCoreRemoteQuery();
        $hash = '';
        $password = 'blurg';
        $user = '';
        $host = 'bar';
        $port = '2095';
        $protocol = 'http';
        $expected = array(
            'hash' => $hash,
            'password' => $password,
            'user' => $user,
            'host' => $host,
            'port' => $port,
            'protocol' => $protocol,
            'disableEnvironmentContext' => true,
        );
        $cs = $this->getCS(null, array(
            $expected
        ));
        $rmeth = new ReflectionMethod($cs, 'initAdapter');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs, $adapter);
        $actual = $r->getUser();
        $this->assertEquals($user, $actual);
    }
    /**
     * @depends testInitAdapterAttemptsToGetVariousEnviromentVarsInternalBeforeSetting
     */
    public function testInitAdapterCallsAdapterInit()
    {
        $hash = '';
        $password = 'blurg';
        $user = '';
        $host = 'bar';
        $port = '2095';
        $protocol = 'http';
        $expected = array(
            'hash' => $hash,
            'password' => $password,
            'user' => $user,
            'host' => $host,
            'port' => $port,
            'protocol' => $protocol,
            'disableEnvironmentContext' => true,
        );
        $adapter = $this->getMock('concreteCoreRemoteQuery', array(
            'init'
        ));
        $adapter->expects($this->once())->method('init')->with($host, $user, $password);
        $cs = $this->getCS(null, array(
            $expected
        ));
        $rmeth = new ReflectionMethod($cs, 'initAdapter');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs, $adapter);
    }
    public function testInitAdapterDoesNotCallAdapterSetPortByDefault()
    {
        $hash = '';
        $password = 'blurg';
        $user = '';
        $host = 'bar';
        $port = '';
        $protocol = 'http';
        $expected = array(
            'hash' => $hash,
            'password' => $password,
            'user' => $user,
            'host' => $host,
            'port' => $port,
            'protocol' => $protocol,
            'disableEnvironmentContext' => true,
        );
        $adapter = $this->getMock('concreteCoreRemoteQuery', array(
            'init',
            'setPort'
        ));
        //must mock init(), since it may call setPort()
        $adapter->expects($this->any())->method('init');
        $adapter->expects($this->never())->method('setPort');
        $cs = $this->getCS(null, array(
            $expected
        ));
        $rmeth = new ReflectionMethod($cs, 'initAdapter');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs, $adapter);
    }
    public function testInitAdapterCallAdapterSetPortWhenPortHasValue()
    {
        $hash = '';
        $password = 'blurg';
        $user = '';
        $host = 'bar';
        $port = '2095';
        $protocol = '';
        $expected = array(
            'hash' => $hash,
            'password' => $password,
            'user' => $user,
            'host' => $host,
            'port' => $port,
            'protocol' => $protocol,
            'disableEnvironmentContext' => true,
        );
        $adapter = $this->getMock('concreteCoreRemoteQuery', array(
            'init',
            'setPort'
        ));
        //must mock init(), since it may call setPort()
        $adapter->expects($this->any())->method('init');
        $adapter->expects($this->once())->method('setPort')->with($port);
        $cs = $this->getCS(null, array(
            $expected
        ));
        $rmeth = new ReflectionMethod($cs, 'initAdapter');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs, $adapter);
    }
    public function testInitAdapterDoesNotCallAdapterSetProtocolByDefault()
    {
        $hash = '';
        $password = 'blurg';
        $user = '';
        $host = 'bar';
        $port = '2095';
        $protocol = '';
        $expected = array(
            'hash' => $hash,
            'password' => $password,
            'user' => $user,
            'host' => $host,
            'port' => $port,
            'protocol' => $protocol,
            'disableEnvironmentContext' => true,
        );
        $adapter = $this->getMock('concreteCoreRemoteQuery', array(
            'init',
            'setProtocol'
        ));
        //must mock init(), since it will likely call setProtocol()
        $adapter->expects($this->any())->method('init');
        //setPort calls setProtocol internally (with a calculated value
        $adapter->expects($this->once())->method('setProtocol')->with('http');
        $cs = $this->getCS(null, array(
            $expected
        ));
        $rmeth = new ReflectionMethod($cs, 'initAdapter');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs, $adapter);
    }
    public function testInitAdapterCallAdapterSetProtocolWhenPortHasNoValueAndProtocolDoes()
    {
        $hash = '';
        $password = 'blurg';
        $user = '';
        $host = 'bar';
        $port = '';
        $protocol = 'http';
        $expected = array(
            'hash' => $hash,
            'password' => $password,
            'user' => $user,
            'host' => $host,
            'port' => $port,
            'protocol' => $protocol,
            'disableEnvironmentContext' => true,
        );
        $adapter = $this->getMock('concreteCoreRemoteQuery', array(
            'init',
            'setProtocol'
        ));
        //must mock init(), since it may call setPort()
        $adapter->expects($this->any())->method('init');
        $adapter->expects($this->once())->method('setProtocol')->with($protocol);
        $cs = $this->getCS(null, array(
            $expected
        ));
        $rmeth = new ReflectionMethod($cs, 'initAdapter');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs, $adapter);
    }
    public function testInitAdapterCallAdapterSetHashWhenHashExists()
    {
        $hash = 'blah';
        $password = 'blurg';
        $user = '';
        $host = 'bar';
        $port = '2095';
        $protocol = '';
        $expected = array(
            'hash' => $hash,
            'password' => $password,
            'user' => $user,
            'host' => $host,
            'port' => $port,
            'protocol' => $protocol,
            'disableEnvironmentContext' => true,
        );
        $adapter = $this->getMock('concreteCoreRemoteQuery', array(
            'init',
            'setHash'
        ));
        //must mock init(), since it may call setPort()
        $adapter->expects($this->any())->method('init');
        $adapter->expects($this->once())->method('setHash')->with($hash);
        $cs = $this->getCS(null, array(
            $expected
        ));
        $rmeth = new ReflectionMethod($cs, 'initAdapter');
        $rmeth->setAccessible(true);
        $r = $rmeth->invoke($cs, $adapter);
    }
    public function testIsLocalQueryReturnsFalseByDefault()
    {
        $cs = $this->getCS();
        $this->assertFalse($cs->isLocalQuery());
    }
    public function testIsLocalQueryReturnsTrueWithEnvVar()
    {
        $this->setLocal();
        $cs = $this->getCS();
        $this->assertTrue($cs->isLocalQuery());
        $this->unsetLocal();
    }
    public function testProtectedUpdateResponseObjectAdapterRequiresRQAndOptionalTakesServiceName()
    {
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, 'updateResponseObjectAdapter');
        $rparams = $rmeth->getParameters();
        $actual = $rparams[0]->getClass()->getName();
        $this->assertEquals($this->qa, $actual);
        $this->assertEquals('adapterName', $rparams[1]->getName());
        $this->assertTrue($rparams[1]->isDefaultValueAvailable());
    }
    public function testProtectedUpdateResponseObjectAdapterReturnsQO()
    {
        $rObj = $this->getRObj();
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, 'updateResponseObjectAdapter');
        $rmeth->setAccessible(true);
        $this->assertInstanceOf($this->qa, $rmeth->invoke($cs, $rObj));
    }
    public function testProtectedUpdateResponseObjectAdapterCallValidAdapterOnServiceArg()
    {
        $rObj = $this->getRObj();
        $aservice = 'blah';
        $cs = $this->getCS(array(
            'validAdapter'
        ));
        $cs->expects($this->once())->method('validAdapter')->with($aservice)->will($this->returnValue(false));
        $rmeth = new ReflectionMethod($cs, 'updateResponseObjectAdapter');
        $rmeth->setAccessible(true);
        $this->assertInstanceOf($this->qa, $rmeth->invoke($cs, $rObj, $aservice));
    }
    public function testProtectedUpdateResponseObjectAdapterGetsDefaultAdapterNameOnInvalidService()
    {
        $rObj = $this->getRObj();
        $aservice = 'blah';
        //        $adapterName = 'Cpanel_Service_Adapter_WHMapi';
        $adapterName = 'foo';
        $cs = $this->getCS(array(
            'validAdapter',
            'getDefaultAdapterName'
        ));
        $cs->expects($this->once())->method('validAdapter')->with($aservice)->will($this->returnValue(false));
        $cs->expects($this->once())->method('getDefaultAdapterName')->will($this->returnValue($adapterName));
        $rmeth = new ReflectionMethod($cs, 'updateResponseObjectAdapter');
        $rmeth->setAccessible(true);
        $rmeth->invoke($cs, $rObj, $aservice);
    }
    public function testProtectedUpdateResponseObjectAdapterGetsDefaultAdapterNameByDefault()
    {
        $rObj = $this->getRObj();
        $aservice = 'blah';
        //        $adapterName = 'Cpanel_Service_Adapter_WHMapi';
        $adapterName = 'foo';
        $cs = $this->getCS(array(
            'validAdapter',
            'getDefaultAdapterName'
        ));
        $cs->expects($this->any())->method('validAdapter');
        $cs->expects($this->once())->method('getDefaultAdapterName')->will($this->returnValue($adapterName));
        $rmeth = new ReflectionMethod($cs, 'updateResponseObjectAdapter');
        $rmeth->setAccessible(true);
        $rmeth->invoke($cs, $rObj);
    }
    /**
     * @depends testProtectedUpdateResponseObjectAdapterGetsDefaultAdapterNameByDefault
     */
    public function testProtectedUpdateResponseObjectAdapterStoreAdapterInQO()
    {
        $rObj = $this->getRObj();
        $aservice = 'blah';
        //        $adapterName = 'Cpanel_Service_Adapter_WHMapi';
        $adapterName = 'foo';
        $cs = $this->getCS(array(
            'getDefaultAdapterName'
        ));
        $cs->expects($this->any())->method('getDefaultAdapterName')->will($this->returnValue($adapterName));
        $rmeth = new ReflectionMethod($cs, 'updateResponseObjectAdapter');
        $rmeth->setAccessible(true);
        $rmeth->invoke($cs, $rObj);
        $this->assertEquals($adapterName, $rObj->query->adapter);
    }
    /**
     * @depends testProtectedUpdateResponseObjectAdapterGetsDefaultAdapterNameByDefault
     */
    public function testProtectedUpdateResponseObjectWillOptimizeForLocalContext()
    {
        $this->setLocal();
        $rObj = $this->getRObj();
        $aservice = 'blah';
        //        $adapterName = 'Cpanel_Service_Adapter_WHMapi';
        $adapterName = 'foo';
        $cs = $this->getCS(array(
            'getDefaultAdapterName'
        ));
        $oAdapterName = $cs::ADAPTER_LIVE;
        $cs->expects($this->any())->method('getDefaultAdapterName')->will($this->returnValue($adapterName));
        $rmeth = new ReflectionMethod($cs, 'updateResponseObjectAdapter');
        $rmeth->setAccessible(true);
        $rmeth->invoke($cs, $rObj);
        $this->assertEquals($oAdapterName, $rObj->query->adapter);
        $this->assertTrue($rObj->query->optimized);
        $this->unsetLocal();
    }
    /**
     * @depends testProtectedUpdateResponseObjectAdapterGetsDefaultAdapterNameByDefault
     */
    public function testProtectedUpdateResponseObjectWillNotSetOptimizeVarForLocalService()
    {
        $this->setLocal();
        $rObj = $this->getRObj();
        $aservice = 'blah';
        $cs = $this->getCS(array(
            'getDefaultAdapterName'
        ));
        $oAdapterName = $cs::ADAPTER_LIVE;
        $cs->expects($this->any())->method('getDefaultAdapterName')->will($this->returnValue($oAdapterName));
        $rmeth = new ReflectionMethod($cs, 'updateResponseObjectAdapter');
        $rmeth->setAccessible(true);
        $rmeth->invoke($cs, $rObj);
        $this->assertEquals($oAdapterName, $rObj->query->adapter);
        $this->assertEmpty($rObj->query->optimized);
        $this->unsetLocal();
    }
    /**
     * @depends testProtectedUpdateResponseObjectAdapterGetsDefaultAdapterNameByDefault
     */
    public function testProtectedUpdateResponseObjectWillNotOptimizeForDisabledLocalService()
    {
        $this->setLocal();
        $rObj = $this->getRObj();
        $aservice = 'blah';
        $adapterName = 'foo';
        $cs = $this->getCS(array(
            'getDefaultAdapterName'
        ));
        $oAdapterName = $cs::ADAPTER_LIVE;
        $cs->expects($this->any())->method('getDefaultAdapterName')->will($this->returnValue($adapterName));
        $rprop = new ReflectionProperty($cs, 'disabledAdapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cs, array(
            $oAdapterName
        ));
        $rmeth = new ReflectionMethod($cs, 'updateResponseObjectAdapter');
        $rmeth->setAccessible(true);
        $rmeth->invoke($cs, $rObj);
        $this->assertNotEquals($oAdapterName, $rObj->query->adapter);
        $this->assertEmpty($rObj->query->optimized);
        $this->unsetLocal();
    }
    /**
     * @depends testProtectedUpdateResponseObjectAdapterReturnsQO
     */
    public function testGenResponesObjectReturnsQO()
    {
        $cs = $this->getCS();
        $rObj = $cs->genResponseObject();
        $this->assertInstanceOf($this->qa, $rObj);
    }
    /**
     * @depends testProtectedUpdateResponseObjectAdapterGetsDefaultAdapterNameByDefault
     */
    public function testGenResponesObjectReturnsQOWithService()
    {
        $adapterName = 'foo';
        $cs = $this->getCS(array(
            'validAdapter',
            'getDefaultAdapterName'
        ));
        $cs->expects($this->any())->method('validAdapter')->will($this->returnValue($adapterName));
        $cs->expects($this->atLeastOnce())->method('getDefaultAdapterName')->will($this->returnValue($adapterName));
        $aservice = $cs->getDefaultAdapterName();
        $rObj = $cs->genResponseObject();
        $this->assertEquals($adapterName, $rObj->query->adapter);
    }
    public function testGetAdapterRequiresRO()
    {
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, 'getAdapter');
        $rparams = $rmeth->getParameters();
        $actual = $rparams[0]->getClass()->getName();
        $this->assertEquals($this->qa, $actual);
    }
    public function testGetAdapterCallsGetDefaultAdapterNameWhenNoAdapterIsSetInRO()
    {
        $rObj = $this->getRObj();
        $cs = $this->getCS(array(
            'getDefaultAdapterName'
        ));
        $oAdapterName = $cs::ADAPTER_LIVE;
        $cs->expects($this->atLeastOnce())->method('getDefaultAdapterName')->will($this->returnValue($oAdapterName));
        $a = $cs->getAdapter($rObj);
        $this->assertEquals($oAdapterName, $rObj->query->adapter);
    }
    public function testGetAdapterDoesNotCallGetDefaultAdapterNameWhenAdapterIsSetInRO()
    {
        $rObj = $this->getRObj();
        $cs = $this->getCS(array(
            'getDefaultAdapterName'
        ));
        $oAdapterName = $cs::ADAPTER_LIVE;
        $rObj->query->adapter = $oAdapterName;
        $cs->expects($this->never())->method('getDefaultAdapterName');
        $a = $cs->getAdapter($rObj);
        $this->assertEquals($oAdapterName, $rObj->query->adapter);
    }
    /**
     * @expectedException Exception
     */
    public function testGetAdapterThroWhenAdapterIsSetInROThatsDisabled()
    {
        $rObj = $this->getRObj();
        $cs = $this->getCS(array(
            'getDefaultAdapterName'
        ));
        $oAdapterName = $cs::ADAPTER_LIVE;
        $rObj->query->adapter = $oAdapterName;
        $cs->expects($this->never())->method('getDefaultAdapterName');
        $rprop = new ReflectionProperty($cs, 'disabledAdapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cs, array(
            $oAdapterName
        ));
        $a = $cs->getAdapter($rObj);
    }
    public function testGetAdapterWillFetchStoredAdapterIfAvailable()
    {
        $rObj = $this->getRObj();
        $cs = $this->getCS(array(
            'getDefaultAdapterName'
        ));
        $oAdapterName = $cs::ADAPTER_WHM;
        $rObj->query->adapter = $oAdapterName;
        $storeda = new Cpanel_Service_Adapter_WHMapi();
        $rprop = new ReflectionProperty($cs, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cs, array(
            $oAdapterName => $storeda
        ));
        $cs->expects($this->never())->method('getDefaultAdapterName');
        $a = $cs->getAdapter($rObj);
        $this->assertEquals(spl_object_hash($storeda), spl_object_hash($a));
    }
    public function testGetAdapterWillSpawnNewAdapterAndStoreAReference()
    {
        $rObj = $this->getRObj();
        $cs = $this->getCS(array(
            'getDefaultAdapterName',
            'spawnAdapter'
        ));
        $oAdapterName = $cs::ADAPTER_WHM;
        $rObj->query->adapter = $oAdapterName;
        $spawneda = new Cpanel_Service_Adapter_WHMapi();
        $cs->expects($this->never())->method('getDefaultAdapterName');
        $cs->expects($this->once())->method('spawnAdapter')->will($this->returnValue($spawneda));
        $a = $cs->getAdapter($rObj);
        $rprop = new ReflectionProperty($cs, 'adapters');
        $rprop->setAccessible(true);
        $storeda = $rprop->getValue($cs);
        $this->assertEquals(spl_object_hash($spawneda), spl_object_hash($a));
        $this->assertEquals(spl_object_hash($storeda[$oAdapterName]), spl_object_hash($a));
    }
    public function argumentData()
    {
        return array(
            array(
                'string',
                1,
                null
            ),
            array(
                new stdClass(),
                1,
                null
            ),
            array(
                1,
                1,
                null
            ),
            array(
                array(),
                1,
                null
            ),
            array(
                array(
                    'one',
                    'two'
                ),
                0,
                'ordinal'
            ),
            array(
                array(
                    'one' => 'one',
                    'two' => 'two',
                ),
                0,
                'associative'
            ),
            array(
                array(
                    'one' => 'one',
                    'two',
                ),
                0,
                'ordinal'
            ),
        );
    }
    /**
     * @dataProvider argumentData
     * @paramsunknown_type $input      
     * @paramsunknown_type $expectExcep
     * @paramsunknown_type $expectType 
     */
    public function testProtectedArrayTypeThrowsOnBadInput($input, $expectExcep, $expectType = null)
    {
        $cs = $this->getCS();
        $rprop = new ReflectionMethod($cs, 'arrayType');
        $rprop->setAccessible(true);
        if ($expectExcep) {
            $this->setExpectedException('Exception');
        }
        $rprop->invoke($cs, $input);
    }
    /**
     * @dataProvider argumentData
     * @paramsunknown_type $input      
     * @paramsunknown_type $expectExcep
     * @paramsunknown_type $expectType 
     * @depends      testClassHasConstants
     * @depends      testProtectedArrayTypeThrowsOnBadInput
     */
    public function testProtectedArrayTypeReturnRightType($input, $expectExcep, $expectType = null)
    {
        if (!is_null($expectType)) {
            $cs = $this->getCS();
            $rprop = new ReflectionMethod($cs, 'arrayType');
            $rprop->setAccessible(true);
            $this->assertEquals($expectType, $rprop->invoke($cs, $input));
        }
    }
    public function testLegacySetUnderscoreTypeUsesStoreAdapter()
    {
        $rObj = $this->getRObj();
        $cs = $this->getCS(array(
            'genResponseObject',
            'getDefaultAdapterName'
        ));
        $type = 'XML';
        $oAdapterName = $cs::ADAPTER_WHM;
        $rObj->query->adapter = $oAdapterName;
        $spawneda = new Cpanel_Service_Adapter_WHMapi();
        $cs->expects($this->never())->method('genResponseObject');
        $cs->expects($this->once())->method('getDefaultAdapterName')->will($this->returnValue($oAdapterName));
        $rprop = new ReflectionProperty($cs, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cs, array(
            $oAdapterName => $spawneda
        ));
        $cs->set_output($type);
    }
    public function testLegacySetUnderscoreTypeAltersAdapter()
    {
        $rObj = $this->getRObj();
        $cs = $this->getCS(array(
            'genResponseObject',
            'getDefaultAdapterName'
        ));
        $type = 'XML';
        $oAdapterName = $cs::ADAPTER_WHM;
        $rObj->query->adapter = $oAdapterName;
        $spawneda = new Cpanel_Service_Adapter_WHMapi();
        $cs->expects($this->never())->method('genResponseObject');
        $cs->expects($this->once())->method('getDefaultAdapterName')->will($this->returnValue($oAdapterName));
        $rprop = new ReflectionProperty($cs, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cs, array(
            $oAdapterName => $spawneda
        ));
        $this->assertNotEquals($type, $spawneda->getResponseFormatType());
        $cs->set_output($type);
        $this->assertEquals($type, $spawneda->getAdapterResponseFormatType());
    }
    public function testLegacySetUnderscoreTypeSpawnsAdapterAsNecessary()
    {
        $rObj = $this->getRObj();
        $cs = $this->getCS(array(
            'genResponseObject',
            'getDefaultAdapterName',
            'getAdapter'
        ));
        $type = 'XML';
        $oAdapterName = $cs::ADAPTER_WHM;
        $rObj->query->adapter = $oAdapterName;
        $spawneda = new Cpanel_Service_Adapter_WHMapi();
        $cs->expects($this->once())->method('genResponseObject')->will($this->returnValue($rObj));
        $cs->expects($this->once())->method('getDefaultAdapterName')->will($this->returnValue($oAdapterName));
        $cs->expects($this->once())->method('getAdapter')->will($this->returnValue($spawneda));
        $cs->set_output($type);
    }
    public function badSetOutputData()
    {
        return array(
            array(
                'blah'
            ),
            array(
                new stdClass()
            ),
            array(
                1
            ),
            array(
                'LiveJSON'
            ),
        );
    }
    /**
     * @dataProvider      badSetOutputData
     * @expectedException Exception
     * @depends           testLegacySetUnderscoreTypeUsesStoreAdapter
     */
    public function testLegacySetUnderscoreTypeThrowOnBadInput($type)
    {
        $rObj = $this->getRObj();
        $cs = $this->getCS(array(
            'genResponseObject',
            'getDefaultAdapterName'
        ));
        $oAdapterName = $cs::ADAPTER_WHM;
        $rObj->query->adapter = $oAdapterName;
        $spawneda = new Cpanel_Service_Adapter_WHMapi();
        $cs->expects($this->never())->method('genResponseObject');
        $cs->expects($this->once())->method('getDefaultAdapterName')->will($this->returnValue($oAdapterName));
        $rprop = new ReflectionProperty($cs, 'adapters');
        $rprop->setAccessible(true);
        $rprop->setValue($cs, array(
            $oAdapterName => $spawneda
        ));
        $cs->set_output($type);
    }
    public function testProtectedCheckParamsHasRightInputArgs()
    {
        $args = array(
            'service',
            'mf',
            'args',
            'method',
            'argType'
        );
        $cs = $this->getCS();
        $rmeth = new ReflectionMethod($cs, 'checkParams');
        $rparams = $rmeth->getParameters();
        foreach ($rparams as $param) {
            $actual[$param->getPosition() ] = $param->getName();
        }
        $this->assertEquals($args, $actual);
    }
    public function checkParamData()
    {
        $api1 = 'ordinal';
        $api2 = 'associative';
        return array(
            array(
                '',
                array(
                    'module' => 'Email',
                    'function' => 'listpopswithdisk',
                    'user' => 'foo'
                ),
                array(
                    'one',
                    'two'
                ),
                'someMethodNameForLogging',
                $api1,
            ),
            array(
                'WHM',
                array(),
                array(
                    'one',
                    'two'
                ),
                'someMethodNameForLogging',
                $api1,
            ),
        );
    }
    /**
     * @dataProvider      checkParamData
     * @depends           testClassHasConstants
     * @depends           testProtectedCheckParamsHasRightInputArgs
     * @expectedException Exception
     */
    public function testProtectedCheckParamsThrowsOnBadInput($service, $mf, $args, $method, $argType)
    {
        $cs = $this->getCS(array(
            'validAdapter'
        ));
        $cs->expects($this->never())->method('validAdapter');
        $rmeth = new ReflectionMethod($cs, 'checkParams');
        $rmeth->setAccessible(true);
        $rmeth->invoke($cs, $service, $mf, $args, $method, $argType);
    }
    public function checkParamData2()
    {
        $api1 = 'ordinal';
        $api2 = 'associative';
        return array(
            array(
                'blah',
                array(
                    'module' => 'Email',
                    'function' => 'listpopswithdisk',
                    'user' => 'foo'
                ),
                array(
                    'one',
                    'two'
                ),
                'someMethodNameForLogging',
                $api1,
                1,
                0,
                0,
            ),
            array(
                'WHM',
                array(
                    'module' => 'Email',
                    'function' => 'listpopswithdisk',
                    'user' => 'foo'
                ),
                array(
                    'one',
                    'two'
                ),
                'someMethodNameForLogging',
                $api1,
                0,
                0,
                0,
            ),
        );
    }
    /**
     * @dataProvider checkParamData2
     * @depends      testProtectedCheckParamsThrowsOnBadInput
     */
    public function testProtectedCheckParamsNormalizesService($service, $mf, $args, $method, $argType, $expectFail, $expectIsLocalQuery, $live)
    {
        if ($live) {
            $this->setLocal();
        }
        $cs = $this->getCS(array(
            'validAdapter',
            'isLocalQuery'
        ));
        if ($expectFail) {
            $this->setExpectedException('Exception');
        }
        $fail = ($expectFail) ? false : $service;
        $cs->expects($this->once())->method('validAdapter')->will($this->returnValue($fail));
        if ($expectIsLocalQuery) {
            $cs->expects($this->once())->method('isLocalQuery')->will($this->returnValue($live));
        } else {
            $cs->expects($this->never())->method('isLocalQuery');
        }
        $rmeth = new ReflectionMethod($cs, 'checkParams');
        $rmeth->setAccessible(true);
        $rmeth->invoke($cs, $service, $mf, $args, $method, $argType);
        if ($live) {
            $this->unsetLocal();
        }
    }
    public function checkParamData3()
    {
        $api1 = 'ordinal';
        $api2 = 'associative';
        return array(
            array(
                'live',
                array(
                    'module' => 'Email',
                    'function' => 'listpopswithdisk',
                    'user' => 'foo'
                ),
                array(
                    'one',
                    'two'
                ),
                'someMethodNameForLogging',
                $api1,
                1,
                1,
                0,
            ),
            array(
                'live',
                array(
                    'module' => 'Email',
                    'function' => 'listpopswithdisk',
                    'user' => 'foo'
                ),
                array(
                    'one',
                    'two'
                ),
                'someMethodNameForLogging',
                $api1,
                0,
                1,
                'live',
            ),
        );
    }
    /**
     * @dataProvider checkParamData3
     * @depends      testProtectedCheckParamsNormalizesService
     */
    public function testProtectedCheckParamsNormalizesLiveService($service, $mf, $args, $method, $argType, $expectFail, $expectIsLocalQuery, $live)
    {
        if ($live) {
            $this->setLocal();
        }
        $cs = $this->getCS(array(
            'validAdapter',
            'isLocalQuery'
        ));
        if ($expectFail) {
            $this->setExpectedException('Exception');
        }
        $cs->expects($this->once())->method('validAdapter')->will($this->returnValue($service));
        $cs->expects($this->once())->method('isLocalQuery')->will($this->returnValue($live));
        $rmeth = new ReflectionMethod($cs, 'checkParams');
        $rmeth->setAccessible(true);
        $rmeth->invoke($cs, $service, $mf, $args, $method, $argType);
        if ($live) {
            $this->unsetLocal();
        }
    }
    public function checkParamData4()
    {
        $api1 = 'ordinal';
        $api2 = 'associative';
        return array(
            array(
                'WHM',
                array(
                    'module' => 'Email',
                    'function' => 'listpopswithdisk',
                    'user' => 'foo'
                ),
                '',
                'someMethodNameForLogging',
                $api1,
                0
            ),
            array(
                'WHM',
                array(
                    'module' => 'Email'
                ),
                '',
                'someMethodNameForLogging',
                $api1,
                1
            ),
            array(
                'WHM',
                array(
                    'module' => 'Email',
                    'function' => 'listpopswithdisk'
                ),
                '',
                'someMethodNameForLogging',
                $api1,
                1
            ),
            array(
                'cpanel',
                array(
                    'module' => 'Email'
                ),
                '',
                'someMethodNameForLogging',
                $api1,
                1
            ),
            array(
                'cpanel',
                array(
                    'module' => 'Email',
                    'function' => 'listpopswithdisk'
                ),
                'string',
                'someMethodNameForLogging',
                $api1,
                1
            ),
            array(
                'cpanel',
                array(
                    'module' => 'Email',
                    'function' => 'listpopswithdisk'
                ),
                new stdClass(),
                'someMethodNameForLogging',
                $api1,
                1
            ),
        );
    }
    /**
     * @dataProvider checkParamData4
     * @depends      testProtectedCheckParamsNormalizesService
     */
    public function testProtectedCheckParamsChecksModuleFunctionArray($service, $mf, $args, $method, $argType, $expectFail)
    {
        $cs = $this->getCS(array(
            'validAdapter'
        ));
        if ($service == 'WHM') {
            $n = $cs::ADAPTER_WHM;
        } else {
            $n = $service;
        }
        $cs->expects($this->once())->method('validAdapter')->will($this->returnValue($n));
        if ($expectFail) {
            $this->setExpectedException('Exception');
        }
        $rmeth = new ReflectionMethod($cs, 'checkParams');
        $rmeth->setAccessible(true);
        $rmeth->invoke($cs, $service, $mf, $args, $method, $argType);
    }
    public function checkParamData5()
    {
        $api1 = 'ordinal';
        $api2 = 'associative';
        return array(
            array(
                'WHM',
                array(
                    'module' => 'Email',
                    'function' => 'listpopswithdisk',
                    'user' => 'foo'
                ),
                array(
                    'one',
                    'two'
                ),
                'someMethodNameForLogging',
                $api1,
                0
            ),
            array(
                'WHM',
                array(
                    'module' => 'Email',
                    'function' => 'listpopswithdisk',
                    'user' => 'foo'
                ),
                array(
                    'one',
                    'two'
                ),
                'someMethodNameForLogging',
                $api2,
                1
            ),
            array(
                'WHM',
                array(
                    'module' => 'Email',
                    'function' => 'listpopswithdisk',
                    'user' => 'foo'
                ),
                array(
                    'one' => 'one',
                    'two' => 'two'
                ),
                'someMethodNameForLogging',
                $api1,
                1
            ),
            array(
                'cpanel',
                array(
                    'module' => 'Email',
                    'function' => 'listpopswithdisk'
                ),
                array(
                    'one',
                    'two'
                ),
                'someMethodNameForLogging',
                $api1,
                0
            ),
            array(
                'cpanel',
                array(
                    'module' => 'Email',
                    'function' => 'listpopswithdisk'
                ),
                array(
                    'one' => 'one',
                    'two' => 'two'
                ),
                'someMethodNameForLogging',
                $api1,
                1,
            ),
        );
    }
    /**
     * @dataProvider checkParamData5
     * @depends      testProtectedCheckParamsNormalizesService
     */
    public function testProtectedCheckParamsChecksArrayType($service, $mf, $args, $method, $argType, $expectFail)
    {
        $cs = $this->getCS(array(
            'validAdapter'
        ));
        if ($service == 'WHM') {
            $n = $cs::ADAPTER_WHM;
        } else {
            $n = $service;
        }
        $cs->expects($this->once())->method('validAdapter')->will($this->returnValue($n));
        if ($expectFail) {
            $this->setExpectedException('Exception');
        }
        $rmeth = new ReflectionMethod($cs, 'checkParams');
        $rmeth->setAccessible(true);
        $rmeth->invoke($cs, $service, $mf, $args, $method, $argType);
    }
}
