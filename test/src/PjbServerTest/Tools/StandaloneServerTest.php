<?php

namespace PjbServerTest\Tools;

use PHPUnit\Framework\TestCase;
use PjbServer\Tools\StandaloneServer;
use PjbServer\Tools\Exception;
use PjbServerTestConfig;

class StandaloneServerTest extends TestCase
{
    /**
     * @var StandaloneServer
     */
    protected $server;

    protected function setUp(): void
    {
        $config = PjbServerTestConfig::getStandaloneServerConfig();
        $this->server = new StandaloneServer($config);
    }

    protected function tearDown(): void
    {
        $this->server->stop($throws_exception = false);
    }

    public function testGetConfig()
    {
        $config = PjbServerTestConfig::getStandaloneServerConfig();
        $server = new StandaloneServer($config);
        self::assertInstanceOf(\PjbServer\Tools\StandaloneServer\Config::class, $server->getConfig());
    }

    public function testIsStarted()
    {
        $pid_file = $this->server->getConfig()->getPidFile();

        self::assertFalse($this->server->isStarted());
        $this->server->start();
        self::assertTrue($this->server->isStarted());
        self::assertFileExists($pid_file);
        $this->server->stop();
        self::assertFalse($this->server->isStarted());
        self::assertFileNotExists($pid_file);
    }

    /*
        public function testStopExceptionWhenStopped()
        {
            $this->server->stop();
            self::expectException(Exception\StopFailedException::class);
            $this->server->stop($throwException=true);

        }

    */
    public function testRestartWhenNotStarted()
    {
        $pid_file = $this->server->getConfig()->getPidFile();

        self::assertFalse($this->server->isStarted());
        $this->server->restart();
        self::assertFileExists($pid_file);
        self::assertTrue($this->server->isStarted());
        $this->server->stop();
        self::assertFileNotExists($pid_file);
        self::assertFalse($this->server->isStarted());
    }

    public function testRestartWhenStarted()
    {
        $pid_file = $this->server->getConfig()->getPidFile();
        $this->server->start();
        self::assertFileExists($pid_file);
        self::assertTrue($this->server->isStarted());
        $this->server->restart();
        self::assertFileExists($pid_file);
        self::assertTrue($this->server->isStarted());
        $this->server->stop();
        self::assertFileNotExists($pid_file);
        self::assertFalse($this->server->isStarted());
    }

    public function testIsProcessRunning()
    {
        $this->server->stop();
        self::assertFalse($this->server->isProcessRunning());
        $this->server->start();
        self::assertTrue($this->server->isProcessRunning());
        $this->server->stop();
    }

    public function testIsProcessRunningThrowsException()
    {
        self::expectException(Exception\PidNotFoundException::class);
        $pid_file = $this->server->getConfig()->getPidFile();
        self::assertFileNotExists($pid_file);
        $this->server->stop();
        self::assertFileNotExists($pid_file);
        $this->server->isProcessRunning($throwException = true);
    }

    public function testGetPid()
    {
        $config = $this->server->getConfig();
        $pid_file = $config->getPidFile();
        $this->server->start();
        $pid = $this->server->getPid();
        self::assertInternalType('int', (filter_var($pid, FILTER_VALIDATE_INT)));
        self::assertFileExists($pid_file);
        self::assertEquals(trim(file_get_contents($pid_file)), $pid);
        $this->server->stop();
    }

    public function testStop()
    {
        $config = $this->server->getConfig();
        $pid_file = $config->getPidFile();
        $this->server->start();
        self::assertFileExists($pid_file);
        $this->server->stop();
        self::assertFileNotExists($pid_file);
    }

    public function testGetOutput()
    {
        $config = $this->server->getConfig();
        $this->server->start();
        $output = $this->server->getOutput();
        self::assertInternalType('string', $output);
        self::assertTrue(strlen($output) > 10);
        $this->server->stop();
    }

    public function testGetOutputThrowsExceptionWhenNoLog()
    {
        self::expectException(\PjbServer\Tools\Exception\RuntimeException::class);
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
            self::assertFalse(true, 'Output log file was not readable, RuntimeException was not thrown');
        } catch (\PjbServer\Tools\Exception\RuntimeException $e) {
            self::assertTrue(true, 'Correctly catched excepted RuntimeException');
        }
        // restore
        chmod($log_file, 0755);
        $this->server->getOutput();
    }

    public function testStopThrowsException()
    {
        self::expectException(\PjbServer\Tools\Exception\RuntimeException::class);
        $this->server->stop(true);
    }

    public function testStartDouble()
    {
        // If already started, a second start should not be
        // a problem

        $this->server->start();
        $this->server->start();
        self::assertTrue(true);
    }

    public function testGetPidCorrupted()
    {
        $config = $this->server->getConfig();
        $pid_file = $config->getPidFile();
        $this->server->start();
        $pid = $this->server->getPid();
        self::assertInternalType('int', $pid);
        // pretend pid file is corrupted
        file_put_contents($pid_file, 'invalidpid');
        try {
            $this->server->getPid();
            self::assertFalse(true, 'PidCorrupted exception was not throwned');
        } catch (\PjbServer\Tools\Exception\PidCorruptedException $e) {
            self::assertTrue(true, 'PID Corrupted exception was correctly thrown');
        }
        // restore pid
        file_put_contents($pid_file, $pid);

        self::assertInternalType('int', $this->server->getPid());
        self::assertEquals($pid, $this->server->getPid());
        $this->server->stop();
    }

    public function testStartServerThrowsPortUnavailableException()
    {
        self::assertFalse($this->server->isStarted());
        $this->server->start();
        self::assertTrue($this->server->isStarted());
        $pid_file = $this->server->getConfig()->getPidFile();
        self::assertFileExists($pid_file);

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
