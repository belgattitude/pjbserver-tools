<?php

namespace PjbServerTest\Tools;

use PjbServer\Tools\StandaloneServer;
use PjbServer\Tools\Exception;
use PjbServerTestConfig;

class StandaloneServerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StandaloneServer
     */
    protected $server;

    protected function setUp()
    {
        $config = PjbServerTestConfig::getStandaloneServerConfig();
        $this->server = new StandaloneServer($config);
    }

    protected function tearDown()
    {
        $this->server->stop($throws_exception = false);
    }

    public function testGetConfig()
    {
        $config = PjbServerTestConfig::getStandaloneServerConfig();
        $server = new StandaloneServer($config);
        $this->assertInstanceOf(\PjbServer\Tools\StandaloneServer\Config::class, $server->getConfig());
    }

    public function testIsStarted()
    {
        $pid_file = $this->server->getConfig()->getPidFile();

        $this->assertFalse($this->server->isStarted());
        $this->server->start();
        $this->assertTrue($this->server->isStarted());
        $this->assertFileExists($pid_file);
        $this->server->stop();
        $this->assertFalse($this->server->isStarted());
        $this->assertFileNotExists($pid_file);
    }

    /*
        public function testStopExceptionWhenStopped()
        {
            $this->server->stop();
            $this->setExpectedException(Exception\StopFailedException::class);
            $this->server->stop($throwException=true);

        }

    */
    public function testRestartWhenNotStarted()
    {
        $pid_file = $this->server->getConfig()->getPidFile();

        $this->assertFalse($this->server->isStarted());
        $this->server->restart();
        $this->assertFileExists($pid_file);
        $this->assertTrue($this->server->isStarted());
        $this->server->stop();
        $this->assertFileNotExists($pid_file);
        $this->assertFalse($this->server->isStarted());
    }

    public function testRestartWhenStarted()
    {
        $pid_file = $this->server->getConfig()->getPidFile();
        $this->server->start();
        $this->assertFileExists($pid_file);
        $this->assertTrue($this->server->isStarted());
        $this->server->restart();
        $this->assertFileExists($pid_file);
        $this->assertTrue($this->server->isStarted());
        $this->server->stop();
        $this->assertFileNotExists($pid_file);
        $this->assertFalse($this->server->isStarted());
    }

    public function testIsProcessRunning()
    {
        $this->server->stop();
        $this->assertFalse($this->server->isProcessRunning());
        $this->server->start();
        $this->assertTrue($this->server->isProcessRunning());
        $this->server->stop();
    }

    public function testIsProcessRunningThrowsException()
    {
        $this->setExpectedException(Exception\PidNotFoundException::class);
        $pid_file = $this->server->getConfig()->getPidFile();
        $this->assertFileNotExists($pid_file);
        $this->server->stop();
        $this->assertFileNotExists($pid_file);
        $this->server->isProcessRunning($throwException = true);
    }

    public function testGetPid()
    {
        $config = $this->server->getConfig();
        $pid_file = $config->getPidFile();
        $this->server->start();
        $pid = $this->server->getPid();
        $this->assertInternalType('int', (filter_var($pid, FILTER_VALIDATE_INT)));
        $this->assertFileExists($pid_file);
        $this->assertEquals(trim(file_get_contents($pid_file)), $pid);
        $this->server->stop();
    }

    public function testStop()
    {
        $config = $this->server->getConfig();
        $pid_file = $config->getPidFile();
        $this->server->start();
        $this->assertFileExists($pid_file);
        $this->server->stop();
        $this->assertFileNotExists($pid_file);
    }

    public function testGetOutput()
    {
        $config = $this->server->getConfig();
        $this->server->start();
        $output = $this->server->getOutput();
        $this->assertInternalType('string', $output);
        $this->assertTrue(strlen($output) > 10);
        $this->server->stop();
    }

    public function testGetOutputThrowsExceptionWhenNoLog()
    {
        $this->setExpectedException(\PjbServer\Tools\Exception\RuntimeException::class);
        $config = $this->server->getConfig();
        $log_file = $config->getLogFile();
        $this->server->start();
        // pretend output log file does not exists
        unlink($log_file);
        $this->server->getOutput();
    }

    public function testGetOutputThrowsExceptionWhenUnreadableLog()
    {
        $config = $this->server->getConfig();
        $log_file = $config->getLogFile();
        $this->server->start();
        // pretend output log file is not readable
        chmod($log_file, 0000);
        try {
            $this->server->getOutput();
            $this->assertFalse(true, 'Output log file was not readable, RuntimeException was not thrown');
        } catch (\PjbServer\Tools\Exception\RuntimeException $e) {
            $this->assertTrue(true, 'Correctly catched excepted RuntimeException');
        }
        // restore
        chmod($log_file, 0755);
        $this->server->getOutput();
    }

    public function testStopThrowsException()
    {
        $this->setExpectedException(\PjbServer\Tools\Exception\RuntimeException::class);
        $this->server->stop(true);
    }

    public function testStartDouble()
    {
        // If already started, a second start should not be
        // a problem

        $this->server->start();
        $this->server->start();
    }

    public function testGetPidCorrupted()
    {
        $config = $this->server->getConfig();
        $pid_file = $config->getPidFile();
        $this->server->start();
        $pid = $this->server->getPid();
        $this->assertInternalType('int', $pid);
        // pretend pid file is corrupted
        file_put_contents($pid_file, 'invalidpid');
        try {
            $this->server->getPid();
            $this->assertFalse(true, 'PidCorrupted exception was not throwned');
        } catch (\PjbServer\Tools\Exception\PidCorruptedException $e) {
            $this->assertTrue(true, 'PID Corrupted exception was correctly thrown');
        }
        // restore pid
        file_put_contents($pid_file, $pid);

        $this->assertInternalType('int', $this->server->getPid());
        $this->assertEquals($pid, $this->server->getPid());
        $this->server->stop();
    }

    public function testStartServerThrowsPortUnavailableException()
    {
        $this->assertFalse($this->server->isStarted());
        $this->server->start();
        $this->assertTrue($this->server->isStarted());
        $pid_file = $this->server->getConfig()->getPidFile();
        $this->assertFileExists($pid_file);

        $this->expectException(Exception\PortUnavailableException::class);

        // Test starting another instance on the same port

        $runningServerConfig = $this->server->getConfig()->toArray();

        // Start the original server
        $this->server->start();

        $config = array_merge($runningServerConfig, [
            'log_file' => $this->server->getConfig()->getLogFile() . '.extra.log',
            'pid_file' => $this->server->getConfig()->getPidFile() . '.extra.pid'
        ]);

        // Other server on same port
        // should throw a port unavailable exception
        $otherServer = new StandaloneServer(new StandaloneServer\Config($config));
        $otherServer->start();
    }
}
