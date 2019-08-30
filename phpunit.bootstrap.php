<?php
require_once dirname(__FILE__) . '/Cpanel/Util/Autoload.php';
use PHPUnit\Framework\TestCase;

/**
 * A TestCase Class specific to cPanel to allow future mocking or overriding
 * @author dustin-s    
 * @covers CpanelTestCase
 */
class CpanelTestCase extends TestCase
{
    protected function _makeMock($className, $methods = array(), $args = array(), $mockName = '', $callConst = true, $callClone = true, $callA = true) {

        $mock = $this->getMockBuilder($className);
        $mock->setMethods($methods);
        $mock->setConstructorArgs($args);
        $mock->setMockClassName($mockName);
        if($callConst){
            $mock->enableOriginalConstructor();
        }else{
            $mock->disableOriginalConstructor();
        }
        if($callClone){
            $mock->enableOriginalClone();
        }else{
            $mock->disableOriginalClone();
        }
        if($callA){
            $mock->enableAutoload();
        }else{
            $mock->disableAutoload();
        }
        return $mock->getMock();

    }

}
