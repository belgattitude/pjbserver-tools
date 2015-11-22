<?php

namespace PjbServer\Tools;

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
    }

    public function testIsStarted()
    {
        $this->assertFalse($this->server->isStarted());
        $this->server->start();
        $this->assertTrue($this->server->isStarted());
        $this->server->stop();
        $this->assertFalse($this->server->isStarted());
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
}
