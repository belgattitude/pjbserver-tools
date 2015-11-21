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
}
