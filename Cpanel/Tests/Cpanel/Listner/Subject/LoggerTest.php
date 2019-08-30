<?php



/**
 * This will include testing for the (concrete) class Cpanel_Listner_Subject_Logger
 * and the (abstract) Cpanel_Abstract_CpanelListner class 
 * @author davidneimeyer
 *         
 */
class Cpanel_Listner_Subject_LoggerTest extends CpanelTestCase
{
    public $cut = 'Cpanel_Listner_Subject_Logger';
    /**
     * Verify construct makes object without arguments
     */
    public function testCanInstantiateWithoutArgument()
    {
        $l = new $this->cut();
        $this->assertInstanceOf($this->cut, $l);
        return $l;
    }
    /**
     * Verify that there's a proper storage space
     * @depends testCanInstantiateWithoutArgument
     */
    public function testHasInternalStorage($l)
    {
        $this->assertClassHasAttribute('observers', $this->cut);
        $rprop = new ReflectionProperty($this->cut, 'observers');
        $rprop->setAccessible(true);
        $storage = $rprop->getValue($l);
        $this->assertInstanceOf('Cpanel_Core_PriorityQueue', $storage);
    }
    public function testAttachTakesTwoArguments()
    {
        $rmeth = new ReflectionMethod($this->cut, 'attach');
        $rparams = $rmeth->getParameters();
        $this->assertEquals(2, count($rparams));
    }
    /**
     * 
     */
    public function testAttachRequiresAnSPLObserver()
    {
        $rmeth = new ReflectionMethod($this->cut, 'attach');
        $rparams = $rmeth->getParameters();
        $actual = $rparams[0]->getClass()->getName();
        $this->assertEquals('SplObserver', $actual);
    }
    public function getMockSplObserver()
    {
        $mockObserver = $this->_makeMock('SplObserver', array(
            'update'
        ));
        return $mockObserver;
    }
    /**
     * @depends testCanInstantiateWithoutArgument
     * @depends testHasInternalStorage
     */
    public function testNewObserverWillBeStored($l)
    {
        $this->assertClassHasAttribute('observers', $this->cut);
        $rprop = new ReflectionProperty($this->cut, 'observers');
        $rprop->setAccessible(true);
        $storage = $rprop->getValue($l);
        $countBefore = count($storage);
        $mockObserver = $this->getMockSplObserver();
        $l->attach($mockObserver);
        $storage = $rprop->getValue($l);
        $countAfter = count($storage);
        $this->assertGreaterThan($countBefore, $countAfter);
        return array(
            $l,
            $rprop,
            $mockObserver
        );
    }
    /**
     * Verify that we attempt look for an observe before storing it; keeps the
     * same observe from having more than one observation per event
     * @depends testNewObserverWillBeStored
     */
    public function testAttachSeeksInternalStorage($fixtureArray)
    {
        list($l, $rprop, $mockObserver) = $fixtureArray;
        $mock = $this->_makeMock($this->cut, array(
            'contains'
        ));
        $mock->expects($this->once())->method('contains')->with($this->anything())->will($this->returnValue(true));
        $mock->attach($mockObserver);
    }
    /**
     * 
     * @depends testNewObserverWillBeStored
     */
    public function testAttachWillCallChangePriorityForStoredObservers($fixtureArray)
    {
        list($l, $rprop, $mockObserver) = $fixtureArray;
        $mock = $this->_makeMock($this->cut, array(
            'contains',
            'changePriority'
        ));
        $mock->expects($this->any())->method('contains')->with($this->anything())->will($this->returnValue(true));
        $mock->expects($this->once())->method('changePriority')->with($mockObserver, 'somevalue');
        $mock->attach($mockObserver, 'somevalue');
    }
    public function testDetachRequiresSplObserver()
    {
        $rmeth = new ReflectionMethod($this->cut, 'detach');
        $rparams = $rmeth->getParameters();
        $actual = $rparams[0]->getClass()->getName();
        $this->assertEquals('SplObserver', $actual);
    }
    /**
     * @depends testCanInstantiateWithoutArgument
     * @depends testHasInternalStorage
     */
    public function testDetachSeeksObserverInStorage()
    {
        $mock = $this->_makeMock($this->cut, array(
            'contains'
        ));
        $mock->expects($this->once())->method('contains')->with($this->anything())->will($this->returnValue(true));
        $mockObserver = $this->getMockSplObserver();
        $mock->detach($mockObserver);
    }
    /**
     * @depends testCanInstantiateWithoutArgument
     * @depends testHasInternalStorage
     */
    public function testDetachRemovesObserverInStorage($l)
    {
        $mockObserver = $this->getMockSplObserver();
        $splstorage = new Cpanel_Core_PriorityQueue();
        $splstorage->attach($mockObserver, 'fake');
        $rprop = new ReflectionProperty($this->cut, 'observers');
        $rprop->setAccessible(true);
        $rprop->setValue($l, $splstorage);
        $countBefore = count($splstorage);
        $l->detach($mockObserver);
        $countAfter = count($rprop->getValue($l));
        $this->assertLessThan($countBefore, $countAfter);
    }
    /**
     * @depends testNewObserverWillBeStored
     *          Enter description here ...
     */
    public function testNotifyCallsUpdateObserver()
    {
        $l = new $this->cut();
        $mockObserver = $this->getMockSplObserver();
        $mockObserver->expects($this->once())->method('update');
        $l->attach($mockObserver);
        $l->notify();
    }
    /**
     * @depends testNewObserverWillBeStored
     */
    public function testNotifyCallsUpdateOnAllObservers()
    {
        $l = new $this->cut();
        $mockObservers[] = $this->getMockSplObserver();
        $mockObservers[] = $this->getMockSplObserver();
        $mockObservers[] = $this->getMockSplObserver();
        foreach ($mockObservers as $mockObserver) {
            $mockObserver->expects($this->once())->method('update');
            $l->attach($mockObserver);
        }
        $l->notify();
    }
    /**
     * @depends         testNotifyCallsUpdateOnAllObservers
     * @outputBuffering disabled
     */
    public function testNotifyCallsUpdateAccordingToPriority()
    {
        $l = new $this->cut();
        $l->order = 'new';
        $mockObservers[2] = array(
            $this->getMockSplObserver(),
            10
        );
        $mockObservers[0] = array(
            $this->getMockSplObserver(),
            2
        );
        $mockObservers[1] = array(
            $this->getMockSplObserver(),
            10
        );
        foreach ($mockObservers as $key => $mockObserver) {
            $mockObserver[0]->expects($this->once())->method('update')->will($this->returnValue($mockObserver[0]));
            $l->attach($mockObserver[0], $mockObserver[1]);
            $expected[$key] = spl_object_hash($mockObserver[0]);
        }
        $called = $l->notify();
        foreach ($called as $observer) {
            $actual[] = spl_object_hash($observer);
        }
        $expected = sort($expected);
        $actual = sort($actual);
        $this->assertEquals($expected, $actual);
    }
    /**
     * Verify that first arg is object instance of SplObserver and exception is
     * thrown without arg2
     */
    public function testChangePriorityRequiresObserverAndPriority()
    {
        $this->expectException('Exception');
        $rmeth = new ReflectionMethod($this->cut, 'changePriority');
        $rparams = $rmeth->getParameters();
        $actual = $rparams[0]->getClass()->getName();
        $this->assertEquals('SplObserver', $actual);
        $l = new $this->cut();
        $obs = $this->getMockSplObserver();
        $l->changePriority($obs);
    }
    /**
     * Verify string are not accepted for priority
     */
    public function testChangePriorityThrowsOnStringForPriority()
    {
        $this->expectException('Exception');
        $l = new $this->cut();
        $obs = $this->getMockSplObserver();
        $l->changePriority($obs, '--fake--');
    }
    /**
     * Verify bool are not accepted for priority
     */
    public function testChangePriorityThrowsOnBoolForPriority()
    {
        $this->expectException('Exception');
        $l = new $this->cut();
        $obs = $this->getMockSplObserver();
        $l->changePriority($obs, true);
    }
    /**
     * Verify objs are not accepted for priority
     */
    public function testChangePriorityThrowsOnObjectForPriority()
    {
        $this->expectException('Exception');
        $l = new $this->cut();
        $obs = $this->getMockSplObserver();
        $obj = new stdClass();
        $l->changePriority($obs, $obj);
    }
    /**
     * Verify number are accepted for priority
     */
    public function testChangePriorityCanTakeNumberForPriority()
    {
        $l = new $this->cut();
        $obs = $this->getMockSplObserver();
        // Ensure that this returns it self and doesn't throw an exception
        $this->assertEquals($l, $l->changePriority($obs, 1));        
    }
    /**
     * Verify numbers in an array are accepted for priority
     */
    public function testChangePriorityCanTakeNumbersInArrayForPriority()
    {
        $l = new $this->cut();
        $obs = $this->getMockSplObserver();
        $value = array(
            1,
            2
        );
        // Ensure that this returns it self and doesn't throw an exception
        $this->assertEquals($l, $l->changePriority($obs, $value));
    }
    /**
     * Verify non-numbers in an array are not accepted for priority
     */
    public function testChangePriorityThrowOnStringInArrayForPriority()
    {
        $this->expectException('Exception');
        $l = new $this->cut();
        $obs = $this->getMockSplObserver();
        $l->changePriority($obs, array(
            1,
            '--fake--'
        ));
    }
    /**
     * Verify non-numbers in an array are not accepted for priority
     */
    public function testChangePriorityThrowOnObjInArrayForPriority()
    {
        $this->expectException('Exception');
        $l = new $this->cut();
        $obs = $this->getMockSplObserver();
        $obj = new stdClass();
        $l->changePriority($obs, array(
            1,
            $obj
        ));
    }
    /**
     * Verify bool in array are not accepted for priority
     */
    public function testChangePriorityThrowsOnBoolInArrayForPriority()
    {
        $this->expectException('Exception');
        $l = new $this->cut();
        $obs = $this->getMockSplObserver();
        $l->changePriority($obs, array(
            1,
            true
        ));
    }
    /**
     * Verify null in array are not accepted for priority
     */
    public function testChangePriorityThrowsOnNullInArrayForPriority()
    {
        $this->expectException('Exception');
        $l = new $this->cut();
        $obs = $this->getMockSplObserver();
        $l->changePriority($obs, array(
            1,
            null
        ));
    }
    /**
     * Verify empty array are not accepted for priority
     */
    public function testChangePriorityThrowsOnEmptyArrayForPriority()
    {
        $this->expectException('Exception');
        $l = new $this->cut();
        $obs = $this->getMockSplObserver();
        $l->changePriority($obs, array());
    }
    /**
     * Verify we altered the priority for stored object
     * @depends testNewObserverWillBeStored
     */
    public function testChangePrioritySetsPriorityInStorage($fixtureArray)
    {
        $l = new $this->cut();
        $rprop = new ReflectionProperty($this->cut, 'observers');
        $rprop->setAccessible(true);;
        $storage = $rprop->getValue($l);
        $mockObserver = $this->getMockSplObserver();
        $l->attach($mockObserver);
        $storage->top();
        $storedInfoBefore = $storage->getInfo();
        $l->changePriority($mockObserver, 9999999978);
        $storedInfoAfter = $storage->getInfo();
        $this->assertNotEquals($storedInfoBefore, $storedInfoAfter);
    }
    public function testLogRequiresTwoArgsAndHasThree()
    {
        $rmeth = new ReflectionMethod($this->cut, 'log');
        $rparams = $rmeth->getParameters();
        $this->assertEquals(3, count($rparams));
        foreach ($rparams as $param) {
            if ($param->getPosition() < 2) {
                $this->assertFalse($param->isDefaultValueAvailable());
            }
        }
    }
    public function testLogSetMessage()
    {
        $l = new $this->cut();
        $expected = array(
            'debug' => 'Fake debug message'
        );
        $this->assertNotEquals($expected, $l->getLog());
        $l->log('debug', 'Fake debug message', false);
        $actual = $l->getLog();
        $this->assertEquals($expected, $actual);
    }
    public function testGetDebugLevelReturnProtectedDebugLevel()
    {
        $l = new $this->cut();
        $expected = 'loud';
        $this->assertNotEquals($expected, $l->getDebugLevel());
        $rprop = new ReflectionProperty($l, 'debugLevel');
        $rprop->setAccessible(true);
        $rprop->setValue($l, $expected);
        $actual = $l->getDebugLevel();
        $this->assertEquals($expected, $actual);
    }
    public function testSetDebugLevelSetsProtectedLevelAndCallsNotify()
    {
        $l = $this->_makeMock($this->cut, array(
            'notify'
        ));
        $l->expects($this->once())->method('notify');
        $level = 'loud';
        $rprop = new ReflectionProperty($l, 'debugLevel');
        $rprop->setAccessible(true);
        $actual = $rprop->getValue($l);
        $this->assertNotEquals($level, $actual);
        $l->setDebugLevel($level);
        $rprop = new ReflectionProperty($l, 'debugLevel');
        $rprop->setAccessible(true);
        $actual = $rprop->getValue($l);
        $this->assertEquals($level, $actual);
    }
}
?>