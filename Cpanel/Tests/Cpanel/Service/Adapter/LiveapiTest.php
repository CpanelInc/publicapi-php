<?php
class Cpanel_Service_Adapter_LiveapiTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Cpanel_Service_Adapter_Liveapi
     */
    protected $live;
    protected $cut = 'Cpanel_Service_Adapter_Liveapi';
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
     * @param unknown_type                   $methods  
     * @param unknown_type                   $args     
     * @param unknown_type                   $mockName 
     * @param unknown_type                   $callConst
     * @param unknown_type                   $callClone
     * @param unknown_type                   $callA    
     *                                                   
     * @note   This method actually returns a Mock Class
     * 
     * @return Cpanel_Service_Adapter_Liveapi
     */
    public function getLive($methods = array(), $args = array(), $mockName = '', $callConst = true, $callClone = true, $callA = true)
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

        $phpPath = exec('which php');
        $cmd = "$phpPath -f $mockserverscript"; //original

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
    public function testCanInstantiateClean()
    {
        $live = $this->getLive(array(
            'openCpanelHandle',
            'closeCpanelHandle'
        ), array(), '', false);
        $live->expects($this->never())->method('openCpanelHandle')->will($this->returnValue(true));
        $live->expects($this->once())->method('closeCpanelHandle')->will($this->returnValue(true));
        $obj = $live->__construct();
        $rprop = new ReflectionProperty($obj, 'connected');
        $rprop->setAccessible(true);
        $rprop->setValue($obj, 1);
        $this->assertInstanceOf($this->cut, $obj);
        $obj->__destruct();
    }
    public function testClassHasConstantDRFT()
    {
        $rclass = new ReflectionClass($this->cut);
        $expected = array(
            'DRFT' => 'LiveJSON'
        );
        $actual = $rclass->getConstants();
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $actual);
            $this->assertEquals($value, $actual[$key]);
        }
    }
    public function testGetAdapterResponseFormatTypeReturnsSomething()
    {
        $live = $this->getLive();
        $rprop = new ReflectionProperty($this->cut, '_adapterResponseFormatType');
        $rprop->setAccessible(true);
        $rprop->setValue($live, 'foo');
        $this->assertEquals('foo', $live->getAdapterResponseFormatType());
    }
    public function testConstructOptionallyTakesRFTAndStoresIt()
    {
        $live = $this->getLive();
        $rprop = new ReflectionProperty($this->cut, '_adapterResponseFormatType');
        $rprop->setAccessible(true);
        $value = $rprop->getValue($live);
        $this->assertEquals(Cpanel_Service_Adapter_Liveapi::DRFT, $value);
        $live->__construct('foo');
        $value = $rprop->getValue($live);
        $this->assertEquals('foo', $value);
        $rprop = new ReflectionProperty($this->cut, '_validRFT');
        $rprop->setAccessible(true);
        $this->assertContains('foo', $rprop->getValue($live));
    }
    /**
     * @expectedException Exception
     */
    public function testSetAdapterResponseFormatTypeThrowOnUnknownType()
    {
        $live = $this->getLive();
        $live->setAdapterResponseFormatType('FooBarFooey');
    }
    /**
     * @depends testClassHasConstantDRFT
     */
    public function testSetAdapterResponseFormatTypeWorksForDRFT()
    {
        $live = $this->getLive();
        $rprop = new ReflectionProperty($this->cut, '_adapterResponseFormatType');
        $rprop->setAccessible(true);
        $rprop->setValue($live, 'foo');
        $live->setAdapterResponseFormatType($live::DRFT);
        $actual = $rprop->getValue($live);
        $this->assertEquals($live::DRFT, $actual);
    }
    public function testRegisterAdapterResponseFormatType()
    {
        $live = $this->getLive();
        $rprop = new ReflectionProperty($this->cut, '_validRFT');
        $rprop->setAccessible(true);
        $live->registerAdapterResponseFormatType('BarBazChewie');
        $this->assertContains('BarBazChewie', $rprop->getValue($live));
    }
    /**
     * @depends testRegisterAdapterResponseFormatType
     */
    public function testSetAdapterResponseFormatTypeWorksOnCustom()
    {
        $live = $this->getLive();
        $live->registerAdapterResponseFormatType('BarBazChewie');
        $live->setAdapterResponseFormatType('BarBazChewie');
    }
    public function testMakeQueryMethodExistsForCUT()
    {
        $this->assertTrue(method_exists($this->cut, 'makeQuery'));
    }
    /**
     * @depends testMakeQueryMethodExistsForCUT
     */
    public function testMakeQueryIsNotDirectlyImplemented()
    {
        $rmeth = new ReflectionMethod($this->cut, 'exec');
        $rclass = new ReflectionClass($this->cut);
        $this->assertNotEquals($rclass->getFilename(), $rmeth->getFilename());
    }
    public function testExecMethodExistsForCUT()
    {
        $this->assertTrue(method_exists($this->cut, 'exec'));
    }
    /**
     * @depends         testExecMethodExistsForCUT
     * @outputBuffering disabled
     */
    public function testExecIsNotDirectlyImplemented()
    {
        $rmeth = new ReflectionMethod($this->cut, 'exec');
        $rclass = new ReflectionClass($this->cut);
        $this->assertNotEquals($rclass->getFilename(), $rmeth->getFilename());
    }
    public function testFetchCallsExecWithCode()
    {
        $var = 'foo';
        $live = $this->getLive(array(
            'exec'
        ));
        $live->expects($this->once())->method('exec')->with('<cpanel print="' . $var . '">')->will($this->returnValue($this->anything()));
        $live->fetch($var);
    }
    public function inputData()
    {
        return array(
            array(
                'api1',
                'exec',
                '1',
                'foo',
                'bar',
                'baz',
                array(
                    'foo',
                    'bar',
                    'baz'
                ),
            ),
            array(
                'api2',
                'exec',
                '2',
                'foo',
                'bar',
                'baz',
                array(
                    'foo',
                    'bar',
                    'baz'
                )
            ),
            array(
                'cpanelif',
                'if',
                '1',
                'if',
                'if',
                '$dollarz',
                array(
                    '$dollarz'
                )
            ),
            array(
                'cpanelfeature',
                'feature',
                '1',
                'feature',
                'feature',
                '!$batz',
                array(
                    '!$batz'
                )
            ),
            array(
                'cpanellangprint',
                'exec',
                '1',
                'langprint',
                '',
                'fooey',
                array(
                    'fooey'
                )
            ),
            array(
                'cpanelprint',
                'exec',
                '1',
                'print',
                '',
                'fooey',
                array(
                    'fooey'
                )
            ),
            array(
                'api',
                'wow',
                '3',
                'haz',
                'all',
                'baz',
                array(
                    'wow',
                    '3',
                    'haz',
                    'all',
                    'baz'
                )
            ),
        );
    }
    /**
     * @dataProvider inputData
     *               
     * @paramsunknown_type $reqtype   
     * @paramsunknown_type $apiversion
     * @paramsunknown_type $module    
     * @paramsunknown_type $func      
     * @paramsunknown_type $args      
     */
    public function testLegacyMethodsCallMakeQuery($method = '', $reqtype = '', $apiversion = '', $module = '', $func = '', $args = '', $call_args = '')
    {
        $live = $this->getLive(array(
            'makeQuery'
        ));
        $live->expects($this->once())->method('makeQuery')->with($reqtype, $apiversion, $module, $func, $args)->will($this->returnValue($this->getRObj()));
        call_user_func_array(array(
            $live,
            $method
        ), $call_args);
    }
    public function testLegacyEnd()
    {
        $live = $this->getLive(array(
            'closeCpanelHandle'
        ));
        $live->expects($this->once())->method('closeCpanelHandle')->will($this->returnValue($this->anything()));
        $live->end();
    }
}
