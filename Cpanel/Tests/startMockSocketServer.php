<?php
/**
 * This script is intended to run in the background while doing tests for the
 * cPanel Live api system.  The Live system opens a unix socket for send/receive
 * with client.  The MockSocketServer does just that.  Tell it a file that it
 * can bind to and it will emulate cpaneld.
 * 
 * Testers should have their testcase exec this file, something like this:
 * <code>
     $n = rand(10e10, 10e14);
    $rand = base_convert($n, 10, 36);
    $socketfile = "/tmp/php-connector-{$rand}.sock";
    putenv("CPANEL_PHPCONNECT_SOCKET={$socketfile}");
    self::$socketfile = $socketfile;
    $dir = dirname ( __FILE__ );
    $script = 'startMockSocketServer.php';
    $mockserverscript = realpath("{$dir}/../../{$script}");
    if(!file_exists($mockserverscript)){
        self::fail("Mock socket server script '$mockserverscript' does not exist");
    }
    $cmd = "/usr/bin/php -f $mockserverscript";
    $arg = "socketfile={$socketfile}";
    $full_cmd = "nohup $cmd $arg > /dev/null 2>&1 & echo $!"; // > /dev/null
    $PID = exec($full_cmd);
    self::$mockSocketServerPID = $PID;
    $lookup = exec("ps -p {$PID} | grep -v 'PID'");
    sleep(2);
    if(empty($lookup)){
        self::fail('Failed to start mock socket server');
    }elseif(!file_exists($socketfile)){
        self::fail('Socket file does not exist: '. $socketfile);
    }
 * </code>
 * NOTE: there's a sleep in there; the socket doesn't take more than a second or
 * so to initialize, however, I found inconsistent results otherwise. 
 */
require_once dirname(__FILE__) . "/MockSocketServer.php";
if (empty($argv)) {
    die('No socket argument found');
}
$socketfile = '';
foreach ($argv as $key => $arg) {
    if (strpos($arg, 'file=') !== false && strpos($arg, '.sock') !== false) {
        $socketfile = substr($arg, (strpos($arg, '=') + 1));
    }
}
if (empty($socketfile)) {
    die('No socket argument found');
}
try {
    $server = new Cpanel_Tests_MockSocketServer();
    $server->setSocketFile($socketfile);
    $server->listen();
}
catch(Exception $e) {
    echo $e->getMessage() . "\n";
    exit(0);
}
exit(1);
