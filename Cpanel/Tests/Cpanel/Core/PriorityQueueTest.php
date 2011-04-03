<?php
/**
 * Test class for Cpanel_Core_PriorityQueue.
 */
class Cpanel_Core_PriorityQueueTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Cpanel_Core_PriorityQueue
     */
    protected $obj;
    protected $cut = 'Cpanel_Core_PriorityQueue';
    protected $obs;
    protected function getNewObserver()
    {
        return new stdClass();
    }
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->obj = new $this->cut();
        $this->obs = $this->getNewObserver();
    }
    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
    /**
     * Verify ArrayAccess count method
     */
    public function testCount()
    {
        $pq = $this->obj;
        $rprop = new ReflectionProperty($this->cut, '_storage');
        $rprop->setAccessible(true);
        $storage = $rprop->getValue($pq);
        $countBefore = count($storage);
        $this->assertEquals($countBefore, $pq->count());
        $this->assertEquals($countBefore, count($pq));
        $storage[] = 'bar';
        $storage[] = 'baz';
        $rprop->setValue($pq, $storage);
        $this->assertEquals(count($storage), $pq->count());
        $this->assertEquals(count($storage), count($pq));
        array_shift($storage);
        $rprop->setValue($pq, $storage);
        $this->assertEquals(count($storage), $pq->count());
        $this->assertEquals(count($storage), count($pq));
    }
    /**
     * Verify storage is altered by attach
     */
    public function testAttachStoresSomething()
    {
        $pq = $this->obj;
        $rprop = new ReflectionProperty($this->cut, '_storage');
        $rprop->setAccessible(true);
        $storage = $rprop->getValue($pq);
        $countBefore = count($storage);
        $mockObserver = $this->getNewObserver();
        $pq->attach($mockObserver, 50);
        $storage = $rprop->getValue($pq);
        $countAfter = count($storage);
        $this->assertGreaterThan($countBefore, $countAfter);
        return array(
            $pq,
            $rprop,
            $mockObserver
        );
    }
    /**
     * Verify Exception throw for string attach
     * @expectedException Exception
     */
    public function testAttachThrowsOnString()
    {
        $this->obj->attach('--fake--', 90);
    }
    /**
     * Verify Exception throw for array attach
     * @expectedException Exception
     */
    public function testAttachThrowsOnArray()
    {
        $this->obj->attach(array(), 99);
    }
    /**
     * Verify Exception throw for bool attach
     * @expectedException Exception
     */
    public function testAttachThrowsOnBool()
    {
        $this->obj->attach(true, 108);
    }
    /**
     * Verify Exception throw for int attach
     * @expectedException Exception
     */
    public function testAttachThrowsOnInt()
    {
        $this->obj->attach(117, 117);
    }
    /**
     * Verify contain return true for stored obj
     * @depends testAttachStoresSomething
     */
    public function testContainsProperlyReturnsTrue()
    {
        $obs = $this->getNewObserver();
        $pq = $this->obj;
        $pq->attach($obs, 128);
        $this->assertTrue($this->obj->contains($obs));
    }
    /**
     * Verify contain return false for not stored obj
     * @depends testAttachStoresSomething
     */
    public function testContainsProperlyReturnsFalse()
    {
        $obs = $this->getNewObserver();
        $pq = $this->obj;
        $this->assertFalse($this->obj->contains($obs));
    }
    /**
     * Verify Exception throw for string contains
     * @expectedException Exception
     */
    public function testContainsThrowsOnString()
    {
        $this->obj->contains('--fake--');
    }
    /**
     * Verify Exception throw for array contains
     * @expectedException Exception
     */
    public function testContainsThrowsOnArray()
    {
        $this->obj->contains(array());
    }
    /**
     * Verify Exception throw for bool contains
     * @expectedException Exception
     */
    public function testContainsThrowsOnBool()
    {
        $this->obj->contains(true);
    }
    /**
     * Verify Exception throw for int contains
     * @expectedException Exception
     */
    public function testContainsThrowsOnInt()
    {
        $this->obj->contains(117);
    }
    /**
     * Verify detach modifies storage
     * @depends testAttachStoresSomething
     * @depends testContainsProperlyReturnsTrue
     * @depends testContainsProperlyReturnsFalse
     */
    public function testDetachWillRemoveStoredObject($fixtureArray)
    {
        list($pq, $rprop, $mockObserver) = $fixtureArray;
        $this->assertTrue($pq->contains($mockObserver));
        $pq->detach($mockObserver);
        $this->assertFalse($pq->contains($mockObserver));
    }
    /**
     * Verify Exception throw for string detach
     * @expectedException Exception
     */
    public function testDetachThrowsOnString()
    {
        $this->obj->detach('--fake--');
    }
    /**
     * Verify Exception throw for array detach
     * @expectedException Exception
     */
    public function testDetachThrowsOnArray()
    {
        $this->obj->detach(array());
    }
    /**
     * Verify Exception throw for bool detach
     * @expectedException Exception
     */
    public function testDetachThrowsOnBool()
    {
        $this->obj->detach(true);
    }
    /**
     * Verify Exception throw for int detach
     * @expectedException Exception
     */
    public function testDetachThrowsOnInt()
    {
        $this->obj->detach(117);
    }
    /**
     * Verify that the next method will advance the storage pointer
     * @depends testAttachStoresSomething
     */
    public function testNext($fixtureArray)
    {
        list($pq, $rprop, $mockObserver) = $fixtureArray;
        $pq->__construct();
        $obss[] = $this->getNewObserver();
        $obss[] = $this->getNewObserver();
        $obss[] = $this->getNewObserver();
        foreach ($obss as $key => $obs) {
            $pq->attach($obs, $key);
        }
        foreach ($obss as $key => $obs) {
            $actual = key($rprop->getValue($pq));
            $this->assertEquals(spl_object_hash($obs), $actual);
            $pq->next();
        }
    }
    /**
     * Verify that valid method
     * @depends testAttachStoresSomething
     * @depends testNext
     */
    public function testValid($fixtureArray)
    {
        list($pq, $rprop, $mockObserver) = $fixtureArray;
        $pq->__construct();
        $obss[] = $this->getNewObserver();
        $obss[] = $this->getNewObserver();
        $obss[] = $this->getNewObserver();
        foreach ($obss as $key => $obs) {
            $pq->attach($obs, $key);
        }
        foreach ($obss as $key => $obs) {
            $this->assertTrue($pq->valid());
            $pq->next();
        }
        $this->assertFalse($pq->valid());
    }
    /**
     * Verify that rewind takes the pointer to the first index and doesn't
     * modify internal structure of storage
     * @depends testAttachStoresSomething
     * @depends testNext
     * @depends testValid
     */
    public function testRewind($fixtureArray)
    {
        list($pq, $rprop, $mockObserver) = $fixtureArray;
        $pq->__construct();
        $obss[] = $this->getNewObserver();
        $obss[] = $this->getNewObserver();
        $obss[] = $this->getNewObserver();
        foreach ($obss as $key => $obs) {
            $pq->attach($obs, $key);
        }
        foreach ($obss as $key => $obs) {
            $actual = key($rprop->getValue($pq));
            $this->assertEquals(spl_object_hash($obs), $actual);
            $pq->next();
        }
        $this->assertFalse($pq->valid());
        $pq->rewind();
        foreach ($obss as $key => $obs) {
            $actual = key($rprop->getValue($pq));
            $this->assertEquals(spl_object_hash($obs), $actual);
            $pq->next();
        }
    }
    /**
     * Verify key method returns proper 
     * @depends testAttachStoresSomething
     * @depends testNext
     */
    public function testKey($fixtureArray)
    {
        list($pq, $rprop, $mockObserver) = $fixtureArray;
        $pq->__construct();
        $obss[] = $this->getNewObserver();
        $obss[] = $this->getNewObserver();
        $obss[] = $this->getNewObserver();
        foreach ($obss as $key => $obs) {
            $pq->attach($obs, $key);
        }
        foreach ($obss as $key => $obs) {
            $actual = key($rprop->getValue($pq));
            $this->assertEquals($key, $pq->key());
            $pq->next();
        }
        $this->assertFalse($pq->key());
    }
    /**
     * @depends           testAttachStoresSomething
     * @expectedException Exception
     */
    public function testPrivateReturnStructureThrowOnObj()
    {
        $rmeth = new ReflectionMethod($this->cut, '_returnStructure');
        $rmeth->setAccessible(true);
        $pq = $this->obj;
        $pq->__construct();
        $obs = $this->getNewObserver();
        $pq->attach($obs, 346);
        $mref = $obs;
        $rmeth->invoke($pq, $mref);
    }
    /**
     * @depends           testAttachStoresSomething
     * @expectedException Exception
     */
    public function testPrivateReturnStructureThrowOnString()
    {
        $rmeth = new ReflectionMethod($this->cut, '_returnStructure');
        $rmeth->setAccessible(true);
        $pq = $this->obj;
        $pq->__construct();
        $obs = $this->getNewObserver();
        $pq->attach($obs, 346);
        $mref = '--fake--';
        $rmeth->invoke($pq, $mref);
    }
    /**
     * @depends           testAttachStoresSomething
     * @expectedException Exception
     */
    public function testPrivateReturnStructureThrowOnInt()
    {
        $rmeth = new ReflectionMethod($this->cut, '_returnStructure');
        $rmeth->setAccessible(true);
        $pq = $this->obj;
        $pq->__construct();
        $obs = $this->getNewObserver();
        $pq->attach($obs, 346);
        $mref = 383;
        $rmeth->invoke($pq, $mref);
    }
    /**
     * @depends           testAttachStoresSomething
     * @expectedException Exception
     */
    public function testPrivateReturnStructureThrowOnBool()
    {
        $rmeth = new ReflectionMethod($this->cut, '_returnStructure');
        $rmeth->setAccessible(true);
        $pq = $this->obj;
        $pq->__construct();
        $obs = $this->getNewObserver();
        $pq->attach($obs, 346);
        $mref = true;
        $rmeth->invoke($pq, $mref);
    }
    /**
     * @depends           testAttachStoresSomething
     * @expectedException Exception
     */
    public function testPrivateReturnStructureThrowOnEmptyArray()
    {
        $rmeth = new ReflectionMethod($this->cut, '_returnStructure');
        $rmeth->setAccessible(true);
        $pq = $this->obj;
        $pq->__construct();
        $obs = $this->getNewObserver();
        $pq->attach($obs, 346);
        $mref = array();
        $rmeth->invoke($pq, $mref);
    }
    public function testConstantsDefined()
    {
        $this->assertTrue(defined("{$this->cut}::EXTR_DATA"));
        $this->assertTrue(defined("{$this->cut}::EXTR_PRIORITY"));
        $this->assertTrue(defined("{$this->cut}::EXTR_BOTH"));
    }
    /**
     * Verify the extract type is set
     */
    public function testSetExtractFlag()
    {
        $pq = $this->obj;
        $rprop = new ReflectionProperty($this->cut, '_flags');
        $rprop->setAccessible(true);
        $expected = $rprop->getValue($pq);
        $pq->setExtractFlag(435);
        $actual = $rprop->getValue($pq);
        $this->assertNotEquals($expected, $actual);
        $rprop->setValue($pq, $expected);
    }
    /**
     * @depends testAttachStoresSomething
     */
    public function testPrivateReturnStructureReturnsSomething()
    {
        $rmeth = new ReflectionMethod($this->cut, '_returnStructure');
        $rmeth->setAccessible(true);
        $pq = $this->obj;
        $pq->__construct();
        $obs = $this->getNewObserver();
        $pq->attach($obs, 346);
        $mref = array(
            'obj' => $obs,
            'inf' => 346
        );
        $this->assertTrue((bool)$rmeth->invoke($pq, $mref));
    }
    /**
     * Verify that the stored object is returned by default
     * @depends testPrivateReturnStructureReturnsSomething
     */
    public function testPrivateReturnStructureReturnsDataObjectByDefault()
    {
        $rmeth = new ReflectionMethod($this->cut, '_returnStructure');
        $rmeth->setAccessible(true);
        $pq = new $this->cut();
        $pq->__construct();
        $obs = $this->getNewObserver();
        $pq->attach($obs, 443);
        $mref = array(
            'obj' => $obs,
            'inf' => 346
        );
        $this->assertEquals(spl_object_hash($obs), spl_object_hash($rmeth->invoke($pq, $mref)));
    }
    /**
     * Verify that the stored object is returned when flag is set
     * @depends testPrivateReturnStructureReturnsSomething
     */
    public function testPrivateReturnStructureReturnsFlagTypeOnRequest()
    {
        $rmeth = new ReflectionMethod($this->cut, '_returnStructure');
        $rmeth->setAccessible(true);
        $pq = $this->obj;
        $pq->__construct();
        $obs = $this->getNewObserver();
        $pq->setExtractFlag(Cpanel_Core_PriorityQueue::EXTR_DATA);
        $pq->attach($obs, 443);
        $mref = array(
            'obj' => $obs,
            'inf' => 443
        );
        $this->assertEquals(spl_object_hash($obs), spl_object_hash($rmeth->invoke($pq, $mref)));
        $pq->setExtractFlag('--fake--');
        $pq->attach($obs, 495);
        $mref = array(
            'obj' => $obs,
            'inf' => 495
        );
        $this->assertEquals(spl_object_hash($obs), spl_object_hash($rmeth->invoke($pq, $mref)));
        $pq->setExtractFlag(Cpanel_Core_PriorityQueue::EXTR_PRIORITY);
        $mref = array(
            'obj' => $obs,
            'inf' => 346
        );
        $this->assertEquals(346, $rmeth->invoke($pq, $mref));
        $pq->setExtractFlag(Cpanel_Core_PriorityQueue::EXTR_BOTH);
        $mref = array(
            'obj' => $obs,
            'inf' => 346
        );
        $r = $rmeth->invoke($pq, $mref);
        $this->assertInternalType('array', $r);
        $this->assertArrayHasKey('data', $r);
        $this->assertArrayHasKey('priority', $r);
        $this->assertEquals(spl_object_hash($obs), spl_object_hash($r['data']));
        $this->assertEquals(346, $r['priority']);
        $pq->setExtractFlag(Cpanel_Core_PriorityQueue::EXTR_DATA);
    }
    /**
     * Verify current method returns currect item pointed to by iterator
     * @depends testAttachStoresSomething
     * @depends testNext
     * @depends testRewind
     * @depends testPrivateReturnStructureReturnsSomething
     */
    public function testCurrent($fixtureArray)
    {
        list($pq, $rprop, $mockObserver) = $fixtureArray;
        $pq = $this->getMock($this->cut, array(
            '_returnStructure'
        ));
        $pq->setExtractFlag(Cpanel_Core_PriorityQueue::EXTR_DATA);
        $loops = 3;
        for ($i = 0; $i < $loops; ++$i) {
            $obs = $this->getNewObserver();
            $obss[$i] = $obs;
            $pq->attach($obs, $i + 1);
        }
        $pq->rewind();
        foreach ($obss as $key => $obs) {
            $this->assertEquals(spl_object_hash($obs), spl_object_hash($pq->current()));
            $pq->next();
        }
    }
    public function intList()
    {
        return array(
            array(
                array(
                    3,
                    1,
                    2
                )
            )
        );
    }
    /**
     * @dataProvider intList
     *               
     * @depends      testAttachStoresSomething
     * @depends      testRewind
     * @depends      testCurrent
     * @depends      testNext
     * @depends      testPrivateReturnStructureReturnsFlagTypeOnRequest
     */
    public function testPrivateSortInt($intList)
    {
        $pq = $this->obj;
        $pq->__construct();
        $loops = count($intList);
        foreach ($intList as $key => $value) {
            $obs = $this->getNewObserver();
            $obss[$key] = array(
                'data' => $obs,
                'priority' => $value
            );
            $sortedobss[$value] = array(
                'data' => $obs,
                'priority' => $value
            );
            $pq->attach($obs, $value);
        }
        $pq->rewind();
        $pq->setExtractFlag(Cpanel_Core_PriorityQueue::EXTR_BOTH);
        foreach ($intList as $key => $value) {
            $r = $pq->current();
            $this->assertEquals($obss[$key], $r);
            $pq->next();
        }
        $rmeth = new ReflectionMethod($this->cut, '_sort');
        $rmeth->setAccessible(true);
        $rmeth->invoke($pq);
        $pq->rewind();
        ksort($sortedobss);
        foreach ($sortedobss as $key => $value) {
            $r = $pq->current();
            $this->assertEquals($value, $r);
            $pq->next();
        }
    }
    public function arrayList1()
    {
        return array(
            array(
                array(
                    array(
                        array(
                            3,
                            2
                        ),
                        array(
                            3,
                            1
                        ),
                        array(
                            3,
                            3
                        ),
                    ),
                    array(
                        array(
                            4,
                            3
                        ), //3
                        array(
                            4,
                            1
                        ), //1
                        array(
                            4
                        ), //4
                        array(
                            4,
                            2
                        ), //2
                        
                    ),
                    array(
                        6, //6
                        array(
                            6,
                            4
                        ), //4
                        array(
                            6,
                            1,
                            0
                        ), //1
                        array(
                            6,
                            1
                        ), //2
                        array(
                            6,
                            3
                        ), //3
                        array(
                            6,
                            5
                        ), //5
                        
                    ),
                )
            )
        );
    }
    /**
     * @dataProvider arrayList1
     *               
     * @depends      testAttachStoresSomething
     * @depends      testRewind
     * @depends      testCurrent
     * @depends      testNext
     * @depends      testPrivateReturnStructureReturnsFlagTypeOnRequest
     */
    public function testPrivateSortArraysWithMixed($arrayLists)
    {
        foreach ($arrayLists as $arrayList) {
            $pq = $this->obj;
            $pq->__construct();
            $obss = array();
            $sortedobss = array();
            foreach ($arrayList as $key => $value) {
                $obs = $this->getNewObserver();
                $obss[$key] = array(
                    'data' => $obs,
                    'priority' => $value
                );
                if (is_array($value)) {
                    $sortkey = end($value);
                    reset($value);
                    // We add 10 here to ensure it's queued last
                    // Yes, an array with fewer elements is smaller than neighboring arrays
                    // (queued at end) think of it as "generic" and therefore less important
                    if (count($value) == 1) {
                        $sortkey = $sortkey + 10;
                    }
                } else {
                    $sortkey = $value;
                }
                $sortedobss[$sortkey] = array(
                    'data' => $obs,
                    'priority' => $value
                );
                $pq->attach($obs, $value);
            }
            ksort($sortedobss);
            $pq->rewind();
            $pq->setExtractFlag(Cpanel_Core_PriorityQueue::EXTR_BOTH);
            foreach ($arrayList as $key => $value) {
                $r = $pq->current();
                $this->assertEquals($obss[$key], $r);
                $pq->next();
            }
            $rmeth = new ReflectionMethod($this->cut, '_sort');
            $rmeth->setAccessible(true);
            $rmeth->invoke($pq);
            $pq->rewind();
            foreach ($sortedobss as $key => $value) {
                $r = $pq->current();
                $this->assertEquals($value, $r);
                $pq->next();
            }
        }
    }
    public function getBadCompareInput()
    {
        return array(
            array(
                new stdClass(),
                675,
            ),
            array(
                679,
                new stdClass(),
            ),
            array(
                new stdClass(),
                new stdClass(),
            ),
            array(
                true,
                688,
            ),
            array(
                691,
                true,
            ),
            array(
                true,
                true,
            ),
        );
    }
    /**
     * @dataProvider      getBadCompareInput
     * @expectedException Exception
     */
    public function testCompareThrowsOnBadInput($a, $b)
    {
        $pq = $this->obj;
        //        $a = $badInput[0];
        //        $b = $badInput[1];
        $pq->compare($a, $b);
    }
    public function getCompareLists()
    {
        return array(
            array(
                array(
                    'inf' => array(
                        3,
                        2
                    )
                ),
                array(
                    'inf' => array(
                        3,
                        1
                    )
                ),
                1,
            ),
            array(
                array(
                    'inf' => array(
                        4,
                        1
                    )
                ), //1
                array(
                    'inf' => array(
                        4
                    )
                ), //2
                 - 1,
            ),
            array(
                array(
                    'inf' => 6
                ), //2
                array(
                    'inf' => array(
                        6,
                        1,
                        0
                    )
                ), //1
                1,
            ),
            array(
                array(
                    'inf' => array(
                        6,
                        1
                    )
                ), //2
                array(
                    'inf' => 1
                ), //1
                1,
            ),
        );
    }
    /**
     * Verify compare returns proper prioritization
     * @dataProvider getCompareLists
     */
    public function testCompare($a, $b, $expected)
    {
        $pq = $this->obj;
        $pq->__construct();
        $actual = $pq->compare($a, $b);
        $this->assertEquals($expected, $actual);
    }
    /**
     * @dataProvider intList
     * @depends      testCompare
     * @depends      testNext
     * @depends      testAttachStoresSomething
     * @depends      testPrivateSortInt
     */
    public function testTop($intList)
    {
        $pq = $this->obj;
        $pq->__construct();
        $loops = count($intList);
        foreach ($intList as $key => $value) {
            $obs = $this->getNewObserver();
            $obss[$key] = array(
                'data' => $obs,
                'priority' => $value
            );
            $sortedobss[$value] = array(
                'data' => $obs,
                'priority' => $value
            );
            $pq->attach($obs, $value);
        }
        $pq->rewind();
        $pq->setExtractFlag(Cpanel_Core_PriorityQueue::EXTR_BOTH);
        foreach ($intList as $key => $value) {
            $r = $pq->current();
            $this->assertEquals($obss[$key], $r);
            $pq->next();
        }
        $pq->top();
        ksort($sortedobss);
        foreach ($sortedobss as $key => $value) {
            $r = $pq->current();
            $this->assertEquals($value, $r);
            $pq->next();
        }
    }
    /**
     * Verify that Extract calls top. top will ensure we're sorted at index 0
     * @depends testAttachStoresSomething
     */
    public function testExtractCallsTop()
    {
        $mock = $this->getMock($this->cut, array(
            'top'
        ));
        $mock->setExtractFlag(Cpanel_Core_PriorityQueue::EXTR_PRIORITY);
        $mock->expects($this->once())->method('top');
        $mock->attach($this->getNewObserver(), 551);
        $mock->attach($this->getNewObserver(), 550);
        $r = $mock->extract();
    }
    /**
     * Verify that Extract calls top. top will ensure we're sorted at index 0
     * @depends testAttachStoresSomething
     * @depends testExtractCallsTop
     * @depends testTop
     * @depends testRewind
     */
    public function testExtractGrabsFirstIndex()
    {
        $pq = $this->obj;
        $pq->__construct();
        $pq->setExtractFlag(Cpanel_Core_PriorityQueue::EXTR_PRIORITY);
        $pq->attach($this->getNewObserver(), 829);
        $pq->attach($this->getNewObserver(), 828);
        $pq->rewind();
        $r = $pq->extract();
        $this->assertEquals(828, $r);
    }
    /**
     * Verify getInfo validates pointer before referencing storage
     */
    public function testGetInfoCallsValid()
    {
        $mock = $this->getMock($this->cut, array(
            'valid'
        ));
        $mock->expects($this->once())->method('valid');
        $mock->getInfo();
    }
    /**
     * @depends testNext
     * @depends testGetInfoCallsValid
     */
    public function testGetInfoReturnsNullOnInvalidPointer()
    {
        $pq = $this->obj;
        $pq->__construct();
        $pq->next();
        $this->assertNull($pq->getInfo());
    }
    /**
     * @depends testAttachStoresSomething
     * @depends testTop
     * @depends testNext
     * @depends testGetInfoCallsValid
     */
    public function testGetInfoReturnsPriority()
    {
        $pq = $this->obj;
        $pq->__construct();
        $pq->attach($this->getNewObserver(), 900);
        $pq->attach($this->getNewObserver(), 901);
        $pq->top();
        $pq->next();
        $actual = $pq->getInfo();
        $this->assertEquals(901, $actual);
    }
    /**
     * @depends testGetInfoReturnsPriority
     */
    public function testSetInfoCallsValid()
    {
        $mock = $this->getMock($this->cut, array(
            'valid'
        ));
        $mock->expects($this->once())->method('valid');
        $mock->setInfo(914);
    }
    /**
     * @depends testGetInfoReturnsPriority
     * @depends testSetInfoCallsValid
     */
    public function testSetInfoReturnsFalseOnInvalidPointer()
    {
        $pq = $this->obj;
        $pq->__construct();
        $pq->next();
        $this->assertFalse($pq->setInfo(926));
    }
    /**
     * @depends testGetInfoReturnsPriority
     * @depends testSetInfoCallsValid
     */
    public function testSetInfoReturnsFalseOnObjArg()
    {
        $pq = $this->obj;
        $pq->__construct();
        $pq->next();
        $this->assertFalse($pq->setInfo(new stdClass()));
    }
    /**
     * @depends testGetInfoReturnsPriority
     * @depends testSetInfoCallsValid
     */
    public function testSetInfoReturnsFalseOnBoolArg()
    {
        $pq = $this->obj;
        $pq->__construct();
        $pq->next();
        $this->assertFalse($pq->setInfo(true));
    }
    /**
     * @depends testGetInfoReturnsPriority
     */
    public function testSetInfoSetPriority()
    {
        $pq = $this->obj;
        $pq->__construct();
        $pq->attach($this->getNewObserver(), 900);
        $pq->top();
        $actual = 916;
        $pq->setInfo($actual);
        $expected = $pq->getInfo();
        $this->assertEquals($expected, $actual);
    }
    /**
     * Verify offsetSet is invoked
     */
    public function testOffsetSet()
    {
        $mock = $this->getMock($this->cut, array(
            'offsetSet'
        ));
        $mock->expects($this->once())->method('offsetSet')->with($this->obs, 613);
        $mock[$this->obs] = 613;
    }
    /**
     * Verify Exception is throw for non object
     * @expectedException Exception
     */
    public function testOffsetSetThrowsOnBlankIndex()
    {
        $obs = $this->getNewObserver();
        $this->obj[] = 69;
    }
    public function testOffsetStores()
    {
        $obs = $this->getNewObserver();
        $rprop = new ReflectionProperty($this->cut, '_storage');
        $rprop->setAccessible(true);
        $this->assertFalse($this->obj->contains($obs));
        $this->obj[$obs] = 69;
        $this->assertTrue($this->obj->contains($obs));
    }
    /**
     * Verify offsetExist uses contains method
     */
    public function testOffsetExists()
    {
        $obs = $this->getNewObserver();
        $mock = $this->getMock($this->cut, array(
            'contains'
        ));
        $mock->expects($this->once())->method('contains')->with($obs);
        $mock->offsetExists($obs);
    }
    /**
     * Verify offsetUnset use detach method
     */
    public function testOffsetUnset()
    {
        $obs = $this->getNewObserver();
        $mock = $this->getMock($this->cut, array(
            'detach'
        ));
        $mock->expects($this->once())->method('detach')->with($obs);
        $mock->offsetUnset($obs);
    }
    /**
     * Verify that offsetGet looks for obj before fetching
     */
    public function testOffsetGetCallsContains()
    {
        $obs = $this->getNewObserver();
        $mock = $this->getMock($this->cut, array(
            'contains'
        ));
        $mock->expects($this->once())->method('contains')->with($obs);
        $mock->offsetGet($obs);
    }
    /**
     * Verify offsetGet return appropriately
     * @depends testAttachStoresSomething
     * @depends testPrivateReturnStructureReturnsFlagTypeOnRequest
     */
    public function testOffsetGetReturnsProperly()
    {
        $pq = $this->obj;
        $pq->__construct();
        $pq->setExtractFlag(Cpanel_Core_PriorityQueue::EXTR_PRIORITY);
        foreach (range(1, 5) as $key) {
            $obs[$key] = $this->getNewObserver();
            $pq->attach($obs[$key], $key);
        }
        $this->assertEquals(4, $pq->offsetGet($obs[4]));
    }
    /**
     * Verify that serialize method returns the serialized representation of
     * the storage
     */
    public function testSerialize()
    {
        $pq = $this->obj;
        $pq->__construct();
        $rprop = new ReflectionProperty($this->cut, '_storage');
        $rprop->setAccessible(true);
        $nstorage = new stdClass();
        $nstorage->foo = 'bar';
        $nstorage->bar = 'baz';
        $rprop->setValue($pq, $nstorage);
        $expected = serialize($nstorage);
        $actual = $pq->serialize();
        $this->assertEquals($expected, $actual);
    }
    /**
     * Verify unserialize will unserialize and replace storage
     */
    public function testUnserialize()
    {
        $pq = $this->obj;
        $pq->__construct();
        $rprop = new ReflectionProperty($this->cut, '_storage');
        $rprop->setAccessible(true);
        $nstorage = new stdClass();
        $nstorage->foo = 'bar';
        $nstorage->bar = 'baz';
        $s = serialize($nstorage);
        $expected = unserialize($s);
        $pq->unserialize($s);
        $actual = $rprop->getValue($pq);
        $this->assertEquals($expected, $actual);
    }
}
?>

