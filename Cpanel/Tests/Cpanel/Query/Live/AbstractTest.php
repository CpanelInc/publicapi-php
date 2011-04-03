<?php
class concreteLocalQuery extends Cpanel_Query_Live_Abstract
{
    public function getAdapterResponseFormatType()
    {
    }
    public function setAdapterResponseFormatType($type)
    {
    }
}
class mockException extends Exception
{
}
/**
 * Test class for Cpanel_Query_Live_Abstract.
 * @covers Cpanel_Query_Live_Abstract
 */
class Cpanel_Query_Live_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Cpanel_Query_Live_Abstract
     */
    protected $lq;
    protected $cut = 'concreteLocalQuery';
    protected $real_cut = 'Cpanel_Query_Live_Abstract';
    protected $qa = 'Cpanel_Query_Object';
    protected static $mockSocketServerPID;
    protected static $socketfile; // "Server" socket address
    protected static $cpanelfh; // "Client" socket resource (for testing the testcase's setup only)
    protected static $fakeResources = array();
    const CLIENT_REQUEST_JSON_ENABLED = '<cpaneljson enable="1">';
    const SERVER_RESPONSE_JSON_ENABLED = "<?xml version=\"1.0\" ?>\n<cpanelresult>{\"data\":{\"result\":\"json\"}}</cpanelresult>\n";
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
     * @return Cpanel_Query_Live_Abstract
     */
    public function getLQ($methods = array(), $args = array(), $mockName = '', $callConst = true, $callClone = true, $callA = true)
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
    //    public static function setUpBeforeClass()
    public function setUp()
    {
        self::startMSS();
    }
    public function tearDown()
    {
        self::stopMSS();
    }
    public static function startMSS()
    {
        $n = rand(10e10, 10e14);
        $rand = base_convert($n, 10, 36);
        $socketfile = "/tmp/php-connector-{$rand}.sock";
        putenv("CPANEL_PHPCONNECT_SOCKET={$socketfile}");
        self::$socketfile = $socketfile;
        $dir = dirname(__FILE__);
        $script = 'startMockSocketServer.php';
        $class = 'MockSocketServer.php';
        $basedir = realpath("{$dir}/../../..");
        $mockserverscript = "{$basedir}/{$script}";
        require_once "{$basedir}/$class";
        if (!file_exists($mockserverscript)) {
            self::fail("Mock socket server script '$mockserverscript' does not exist");
        }
        $cmd = "/usr/bin/php -f $mockserverscript";
        $arg = "socketfile={$socketfile}";
        $full_cmd = "nohup $cmd $arg > /dev/null 2>&1 & echo $!"; // > /dev/null
        $PID = exec($full_cmd);
        self::$mockSocketServerPID = $PID;
        $lookup = exec("ps -p {$PID} | grep -v 'PID'");
        sleep(1);
        if (empty($lookup)) {
            self::fail('Failed to start mock socket server');
        } elseif (!file_exists($socketfile)) {
            self::fail('Socket file does not exist: ' . $socketfile);
        }
    }
    public static function tearDownAfterClass()
    {
        if (!empty(self::$fakeResources)) {
            foreach (self::$fakeResources as $name => $fh) {
                fclose($fh);
                if (strpos($name, 'publicapi-test-resource') !== false && file_exists($name)) {
                    unlink($name);
                }
            }
        }
    }
    public static function stopMSS()
    {
        if (self::$mockSocketServerPID) {
            exec('kill ' . self::$mockSocketServerPID . ' >/dev/null 2>&1');
        }
        if (file_exists(self::$socketfile) && strpos(self::$socketfile, '/tmp') !== false && substr(self::$socketfile, -5) == '.sock') {
            exec('rm -f ' . self::$socketfile . ' >/dev/null 2>&1');
        }
    }
    public static function respawnMSS()
    {
        self::stopMSS();
        sleep(1);
        self::startMSS();
    }
    public function readMSSLog()
    {
        $logfile = Cpanel_Tests_MockSocketServer::LOG_FILE;
        if (file_exists($logfile)) {
            return file_get_contents($logfile);
        } else {
            return false;
        }
    }
    private function _makeFakeResource()
    {
        $n = rand(10e10, 10e14);
        $rand = base_convert($n, 10, 36);
        $file = "/tmp/publicapi-test-resource-{$rand}.file";
        $fh = fopen($file, 'w+');
        self::$fakeResources[$file] = $fh;
        return $fh;
    }
    private function _makeFsockResource()
    {
        self::$cpanelfh = fsockopen("unix://" . self::$socketfile);
    }
    private function _closeFsockResource()
    {
        fclose(self::$cpanelfh);
    }
    /**
     * Emulate basics that cut should have.
     * This is primarily for testing the mock socket server
     * 
     * NOTE: this will leave the socket open! YOU must close socket before the 
     * cut tries to write to it!!!  The best/easiest thing to do is perform 
     * _readSocket() [if you truely expect the mock socket server to respond] or
     * _closeFsockResource() [if your just testing that you can open the unix
     * socket file];
     * 
     * @param unknown_type $code
     * 
     * @return bool        
     */
    private function _writeSocket($code)
    {
        $this->_makeFsockResource();
        $status = fwrite(self::$cpanelfh, strlen($code) . "\n" . $code);
        if (!$status) {
            $this->throwException('Testcase unable to write to mock socket server.');
        }
        return (bool)$status;
    }
    /**
     * Emulate basics that cut should have.
     * This is primarily for testing the mock socket server
     * 
     * NOTE: this will clos the socket! YOU must close socket before the 
     * cut tries to write to it!!!
     * 
     */
    private function _readSocket()
    {
        $buffer = '';
        $result = '';
        while ($buffer = fgets(self::$cpanelfh)) {
            $result = $result . $buffer;
            if (strpos($buffer, '</cpanelresult>') !== false) {
                break;
            }
        }
        $this->_closeFsockResource();
        return $result;
    }
    /**
     * Verify our setUp set the env var the the client would have
     */
    public function testTestCaseSetUpEnvSet()
    {
        $envSockValue = getenv('CPANEL_PHPCONNECT_SOCKET');
        $this->assertEquals(self::$socketfile, $envSockValue);
    }
    /**
     * Verify we can write/read our faux cPanel server socket 
     * 
     */
    public function testTestCaseOpenSocketWriteRead()
    {
        $reqStr = self::CLIENT_REQUEST_JSON_ENABLED;
        $respStr = self::SERVER_RESPONSE_JSON_ENABLED;
        // Send data over socket
        $status = $this->_writeSocket($reqStr);
        $this->assertTrue($status);
        // Read data that came to "server"
        $recv = $this->_readSocket();
        $this->assertEquals($respStr, $recv);
    }
    /**
     * 
     */
    public function testCanInstantiateClean()
    {
        $lq = $this->getLQ(array(
            'openCpanelHandle',
            'closeCpanelHandle'
        ), array(), '', false);
        $lq->expects($this->never())->method('openCpanelHandle')->will($this->returnValue(true));
        $lq->expects($this->once())->method('closeCpanelHandle')->will($this->returnValue(true));
        $obj = $lq->__construct();
        $rprop = new ReflectionProperty($obj, 'connected');
        $rprop->setAccessible(true);
        $rprop->setValue($obj, 1);
        $this->assertInstanceOf($this->cut, $obj);
        $obj->__destruct();
    }
    /**
     * @expectedException Exception
     */
    public function testConstructThrowsOnBadInput()
    {
        $lq = $this->getLQ();
        $rmeth = new ReflectionMethod($this->cut, '__construct');
        $rmeth->invoke($lq, new stdClass());
    }
    public function testConstructCallsSetResponseObjectOnValidInput()
    {
        $rObj = $this->getRObj();
        $lq = $this->getLQ(array(
            'setResponseObject'
        ));
        $lq->expects($this->once())->method('setResponseObject')->with($rObj);
        $rmeth = new ReflectionMethod($this->cut, '__construct');
        $rmeth->invoke($lq, $rObj);
    }
    /** 
     *  closeCpanelHandle should:
     *  1) write the shutdown string to socket
     *  2) run the safeClose function which tries to close socket gracefully
     *  
     *  We need to inject a filehandle, and store the socketfile location for
     *  closeCpanelHandle to really do either of those.
     *  Since we can't mock _write() [it's private] we can only verify the the
     *  server closed is self [due to receiving the shutdown string]
     * @outputBuffering disabled
     */
    public function testCloseCpanelHandleWillAttemptToShutdownServer()
    {
        $PID = self::$mockSocketServerPID;
        $lookup = exec("ps -p {$PID} | grep -v 'PID'");
        if (empty($lookup)) {
            $this->markTestSkipped('Failed to find mock socket server. Follow assertions are likely to fail.');
        }
        $lq = $this->getLQ(array(
            'safeClose'
        ));
        $rprop = new ReflectionProperty($this->cut, 'connected');
        $rprop->setAccessible(true);
        $rprop->setValue($lq, 1);
        //make a fsockopen handle and inject it
        $this->_makeFsockResource();
        $this->assertTrue(is_resource(self::$cpanelfh));
        $rprop = new ReflectionProperty($this->real_cut, '_cpanelfh');
        $rprop->setAccessible(true);
        $rprop->setValue($lq, self::$cpanelfh);
        // inject the socketfile name
        $rprop = new ReflectionProperty($this->cut, 'socketfile');
        $rprop->setAccessible(true);
        $rprop->setValue($lq, self::$socketfile);
        $lq->expects($this->once())->method('safeClose');
        $r = $lq->closeCpanelHandle();
        sleep(1);
        $PID = self::$mockSocketServerPID;
        $lookup = exec("ps -p {$PID} | grep -v 'PID'");
        if (!empty($lookup)) {
            $this->fail('Failed send shutdown string to mock socket server');
        }
    }
    /**
     * We can indirectly test that openCpanelHandle returns early by checking
     * mock socket server log for various data, like 'accepting connections'
     */
    public function testOpenCpanelHandleWillReturnIfConnectedAndSocketExists()
    {
        $log1 = $this->readMSSLog();
        $lq = $this->getLQ();
        $log2 = $this->readMSSLog();
        $this->assertEquals($log1, $log2);
        $rprop = new ReflectionProperty($this->cut, 'connected');
        $rprop->setAccessible(true);
        $rprop->setValue($lq, 1);
        $fh = $this->_makeFakeResource();
        $rprop = new ReflectionProperty($this->real_cut, '_cpanelfh');
        $rprop->setAccessible(true);
        $rprop->setValue($lq, $fh);
        $lq->openCpanelHandle();
        $log3 = $this->readMSSLog();
        $this->assertEquals($log2, $log3);
    }
    /**
     * like other tests, we can monitor the mss log for this change
     */
    public function testOpenCpanelHandleInvokesPrivateIntJSONMode()
    {
        $lq = $this->getLQ();
        $log1 = $this->readMSSLog();
        $condition = (bool)strpos($log1, Cpanel_Tests_MockSocketServer::C_ENABLE_JSON);
        $this->assertFalse($condition);
        $lq->openCpanelHandle();
        $log2 = $this->readMSSLog();
        $condition = (bool)strpos($log2, Cpanel_Tests_MockSocketServer::C_ENABLE_JSON);
        $this->assertTrue($condition);
    }
    /**
     * @expectedException Exception
     */
    public function testPrivateIntJSONModeThrowsIfNotConnected()
    {
        $lq = $this->getLQ();
        $rmeth = new ReflectionMethod($lq, '_initJSONMode');
        $rmeth->setAccessible(true);
        $rmeth->invoke($lq);
    }
    /**
     * @expectedException Exception
     */
    public function testOpenCpanelHandleThrowsOnBadSocketfile()
    {
        $lq = $this->getLQ();
        putenv('CPANEL_PHPCONNECT_SOCKET=');
        $lq->openCpanelHandle();
    }
    /**
     * looks like this test passed due to warn by fsockopen, and not the scripted
     * exception throw
     * @todo              consider reworking or removing; might be untestable
     * @expectedException Exception
     */
    public function testOpenCpanelHandleThrowsOnBadfsockopen()
    {
        $lq = $this->getLQ();
        putenv('CPANEL_PHPCONNECT_SOCKET=/dev/null');
        $lq->openCpanelHandle();
    }
    /**
     * @expectedException Exception
     */
    public function testSetResponseObjectThrowsOnBadInput()
    {
        $lq = $this->getLQ();
        $lq->setResponseObject(new stdClass());
    }
    public function testSetResponseObjectDoesSomething()
    {
        $rObj = $this->getRObj();
        $lq = $this->getLQ(array(
            'setOptions'
        ));
        $lq->expects($this->once())->method('setOptions')->with(array(
            'responseObject' => $rObj
        ));
        $lq->setResponseObject($rObj);
    }
    /**
     * @depends testSetResponseObjectDoesSomething
     */
    public function testGetResponseObject()
    {
        $rObj = $this->getRObj();
        $lq = $this->getLQ();
        $lq->setResponseObject($rObj);
        $storedRObj = $lq->getResponseObject();
        $expected = spl_object_hash($rObj);
        $actual = spl_object_hash($storedRObj);
        $this->assertEquals($expected, $actual);
    }
    public function testSafeClose()
    {
        $lq = $this->getLQ();
        $lq->openCpanelHandle();
        $rprop = new ReflectionProperty($this->real_cut, '_cpanelfh');
        $rprop->setAccessible(true);
        $actual = $rprop->getValue($lq);
        $this->assertInternalType('resource', $actual);
        $rmeth = new ReflectionMethod($this->real_cut, '_write');
        $rmeth->setAccessible(true);
        $rmeth->invoke($lq, Cpanel_Tests_MockSocketServer::C_ENABLE_JSON);
        $lq->safeClose();
        $rprop = new ReflectionProperty($this->real_cut, '_cpanelfh');
        $rprop->setAccessible(true);
        $actual = $rprop->getValue($lq);
        $this->assertNull($actual);
    }
    /**
     * @expectedException mockException
     */
    public function testExecCallsOpenCpanelHandleIfNotConnected()
    {
        $lq = $this->getLQ(array(
            'openCpanelHandle'
        ));
        $lq->expects($this->once())->method('openCpanelHandle')->will($this->throwException(new mockException('mocking for premature exit')));
        $lq->exec('blah');
    }
    public function testExecTakesResponseObject()
    {
        $rObj = $this->getRObj(true, array(
            'getResponseFormatType'
        ));
        $rObj->setResponseFormatType(Cpanel_Service_Adapter_Liveapi::DRFT);
        $parser = $rObj->getResponseParser();
        $store = $parser::PARSER_TYPE;
        $rObj->query->$store = Cpanel_Tests_MockSocketServer::C_ENABLE_JSON;
        $rObj->expects($this->once())->method('getResponseFormatType')->will($this->returnValue($store));
        $lq = $this->getLQ();
        $lq->exec($rObj, 1);
    }
    public function testExecTakesCodeInputString()
    {
        $code = Cpanel_Tests_MockSocketServer::C_ENABLE_JSON;
        $lq = $this->getLQ();
        $lq->setResponseObject($this->getRObj());
        $lq->exec($code, 1);
    }
    /**
     * @expectedException Exception
     */
    public function testExecRequiresResponseObjectBeSet()
    {
        $code = Cpanel_Tests_MockSocketServer::C_ENABLE_JSON;
        $lq = $this->getLQ(array(
            'getResponseObject'
        ));
        $lq->expects($this->once())->method('getResponseObject')->will($this->returnValue($this->anything()));
        $lq->exec($code, 1);
    }
    /**
     * @expectedException Exception
     */
    public function testExecTakesCodeThrowOnBadInput()
    {
        $code = Cpanel_Tests_MockSocketServer::C_ENABLE_JSON;
        $lq = $this->getLQ();
        $lq->setResponseObject($this->getRObj());
        $lq->exec(array(
            'blah'
        ), 1);
    }
    /**
     * @expectedException Exception
     */
    public function testExecThrowsOnEmptyComputedCodeString()
    {
        $rObj = $this->getRObj(true, array(
            'getResponseFormatType'
        ));
        $rObj->setResponseFormatType(Cpanel_Service_Adapter_Liveapi::DRFT);
        $parser = $rObj->getResponseParser();
        $store = $parser::PARSER_TYPE;
        $rObj->query->$store = '';
        $rObj->expects($this->any())->method('getResponseFormatType')->will($this->returnValue($store));
        $lq = $this->getLQ();
        $lq->exec($rObj, 1);
    }
    public function testExecReturnNullAndDoesNotParseResponseOnSkipReturn()
    {
        $rObj = $this->getRObj(true, array(
            'parse'
        ));
        $rObj->setResponseFormatType(Cpanel_Service_Adapter_Liveapi::DRFT);
        $parser = $rObj->getResponseParser();
        $store = $parser::PARSER_TYPE;
        $rObj->query->$store = Cpanel_Tests_MockSocketServer::C_ENABLE_JSON;
        $rObj->expects($this->never())->method('parse')->will($this->returnValue(true));
        $lq = $this->getLQ();
        $lq->exec($rObj, 1);
    }
    public function testExecParsesResponseByDefault()
    {
        $rObj = $this->getRObj(true, array(
            'parse'
        ));
        $rObj->setResponseFormatType(Cpanel_Service_Adapter_Liveapi::DRFT);
        $parser = $rObj->getResponseParser();
        $store = $parser::PARSER_TYPE;
        $rObj->query->$store = Cpanel_Tests_MockSocketServer::C_ENABLE_JSON;
        $rObj->expects($this->once())->method('parse')->will($this->returnValue(true));
        $lq = $this->getLQ();
        $lq->exec($rObj);
    }
    public function testMakeQueryInputArguments()
    {
        $rmeth = new ReflectionMethod($this->cut, 'makeQuery');
        $args = array(
            'reqtype',
            'version',
            'module',
            'func',
            'args'
        );
        $defaults['args'] = array();
        $actual = array();
        foreach ($rmeth->getParameters() as $param) {
            $actual[$param->getPosition() ] = $param->getName();
            if (array_key_exists($param->getName(), $defaults)) {
                $this->assertTrue($param->isDefaultValueAvailable());
                $this->assertEquals($defaults[$param->getName() ], $param->getDefaultValue());
            } else {
                $this->assertFalse($param->isOptional());
            }
        }
        $this->assertEquals($args, $actual);
    }
    /**
     * @expectedException Exception
     */
    public function testMakeQueryOptionalArgsMustBeArray()
    {
        $lq = $this->getLQ(array(
            'exec'
        ));
        $lq->expects($this->never())->method('exec')->will($this->returnValue($this->anything()));
        $rObj = $this->getRObj();
        $rObj->setResponseFormatType(Cpanel_Service_Adapter_Liveapi::DRFT);
        $lq->setResponseObject($rObj);
        $lq->makeQuery('exec', 2, 'Test', 'func', 'string!');
    }
    /**
     * we allow non-'exec' to be strings....legacy, probably best this way due
     * to the php parser in Cpanel
     */
    public function testMakeQueryOptionalArgsMustBeArray2()
    {
        $lq = $this->getLQ(array(
            'exec'
        ));
        $lq->expects($this->once())->method('exec')->will($this->returnValue($this->anything()));
        $rObj = $this->getRObj();
        $rObj->setResponseFormatType(Cpanel_Service_Adapter_Liveapi::DRFT);
        $lq->setResponseObject($rObj);
        $lq->makeQuery('if', 1, 'print', 'func', 'string!');
    }
    public function testMakeQueryCallsGetResponseObject()
    {
        $rObj = $this->getRObj();
        $rObj->setResponseFormatType(Cpanel_Service_Adapter_Liveapi::DRFT);
        $lq = $this->getLQ(array(
            'getResponseObject'
        ));
        $lq->expects($this->once())->method('getResponseObject')->will($this->returnValue($rObj));
        $lq->makeQuery('exec', 2, 'Test', 'func', array());
    }
    public function testMakeQueryCallsExecWithResponseObject()
    {
        $rObj = $this->getRObj();
        $rObj->setResponseFormatType(Cpanel_Service_Adapter_Liveapi::DRFT);
        $lq = $this->getLQ(array(
            'exec'
        ));
        $lq->setResponseObject($rObj);
        $lq->expects($this->once())->method('exec')->with($rObj)->will($this->returnValue($this->anything()));
        $lq->makeQuery('exec', 2, 'Test', 'func', array(
            'foo'
        ));
    }
    /**
     * @expectedException Exception
     */
    public function testMagicCallWillAlwaysThrow()
    {
        $lq = $this->getLQ();
        $lq->getBlah();
    }
}
