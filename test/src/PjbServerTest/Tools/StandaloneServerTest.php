<?php

namespace PjbServerTest\Tools;

use PjbServer\Tools\StandaloneServer;
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
        $this->server->stop();
    }

    public function testIsStarted()
    {
        $this->assertFalse($this->server->isStarted());
        $this->server->start();
        $this->assertTrue($this->server->isStarted());
        $this->server->stop();
        $this->assertFalse($this->server->isStarted());
    }

    public function testRestartWhenNotStarted()
    {
        $this->assertFalse($this->server->isStarted());
        $this->server->restart();
        $this->assertTrue($this->server->isStarted());
        $this->server->stop();
        $this->assertFalse($this->server->isStarted());
    }

    public function testRestartWhenStarted()
    {
        $this->server->start();
        $this->assertTrue($this->server->isStarted());
        $this->server->restart();
        $this->assertTrue($this->server->isStarted());
        $this->server->stop();
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


    public function testGetPid()
    {
        $config   = $this->server->getConfig();
        $pid_file = $config['pid_file'];
        $this->server->start();
        $pid = $this->server->getPid();
        $this->assertInternalType('int', (filter_var($pid, FILTER_VALIDATE_INT)));
        $this->assertFileExists($pid_file);
        $this->assertEquals(trim(file_get_contents($pid_file)), $pid);
        $this->server->stop();
    }

    public function testStop()
    {
        $config   = $this->server->getConfig();
        $pid_file = $config['pid_file'];
        $this->server->start();
        $this->assertFileExists($pid_file);
        $this->server->stop();
        $this->assertFileNotExists($pid_file);
    }

    public function testGetOutput()
    {
        $config   = $this->server->getConfig();
        $pid_file = $config['pid_file'];
        $this->server->start();
        $output = $this->server->getOutput();
        $this->assertInternalType('string', $output);
        $this->assertTrue(strlen($output) > 10);
        $this->server->stop();
    }

    public function testGetOutputThrowsExceptionWhenNoLog()
    {
        $this->expectException(\PjbServer\Tools\Exception\RuntimeException::class);
        $config   = $this->server->getConfig();
        $log_file = $config['log_file'];
        $this->server->start();
        // pretend output log file does not exists
        unlink($log_file);
        $this->server->getOutput();
    }

    public function testGetOutputThrowsExceptionWhenUnreadbableLog()
    {
        $config   = $this->server->getConfig();
        $log_file = $config['log_file'];
        $this->server->start();
        // pretend output log file is not readable
        chmod($log_file, 0000);
        try {
            $this->server->getOutput();
            $this->assertFalse(true, 'Output log file was not readable, RuntimeException was not thrown');
        } catch (\PjbServer\Tools\Exception\RuntimeException $e) {
        }
        // restore
        chmod($log_file, 0755);
        $this->server->getOutput();
    }


    public function testStopThrowsException()
    {
        $this->expectException(\PjbServer\Tools\Exception\RuntimeException::class);
        $this->server->stop(true);
    }

    public function testStartDouble()
    {
        $this->server->start();
        $this->server->start();
    }

    public function testGetPidCorrupted()
    {
        $config   = $this->server->getConfig();
        $pid_file = $config['pid_file'];
        $this->server->start();
        $pid = $this->server->getPid();
        $this->assertInternalType('int', $pid);
        // pretend pid file is corrupted
        file_put_contents($pid_file, 'invalidpid');
        try {
            $this->server->getPid();
            $this->assertFalse(true, "PidCorrupted exception was not throwned");
        } catch (\PjbServer\Tools\Exception\PidCorruptedException $e) {
            $this->assertTrue(true, "PID Corrupted exception was correctly thrown");
        }
        // restore pid
        file_put_contents($pid_file, $pid);

        $this->assertInternalType('int', $this->server->getPid());
        $this->assertEquals($pid, $this->server->getPid());
        $this->server->stop();
    }
}
