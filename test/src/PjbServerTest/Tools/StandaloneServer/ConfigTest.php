<?php

namespace PjbServerTest\Tools\StandaloneServer;

use PjbServer\Tools\StandaloneServer\Config;
use PjbServerTestConfig;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testConstructorThrowsInvalidArgumentException()
    {
        $this->setExpectedException(\PjbServer\Tools\Exception\InvalidArgumentException::class);
        $params = [];
        $config = new Config($params);
    }

    public function testConstructorThrowsInvalidArgumentException2()
    {
        $this->setExpectedException(\PjbServer\Tools\Exception\InvalidArgumentException::class);
        $params = ['port' => 'cool'];
        $config = new Config($params);
    }

    public function testConstructorThrowsInvalidArgumentException3()
    {
        $this->setExpectedException(\PjbServer\Tools\Exception\InvalidArgumentException::class);
        $params = [
            'port' => '8192',
            'server_jar' => '/invalid_path/JavaBridge.jar'
        ];
        $config = new Config($params);
    }

    public function testConstructorThrowsInvalidArgumentException4()
    {
        $this->setExpectedException(\PjbServer\Tools\Exception\InvalidArgumentException::class);
        $params = [
            'error_file' => '/invalid_path/pjb621_standalone/logs/error.log',
        ];
        $config = new Config($params);
    }

    public function testConstructorThrowsInvalidArgumentException5()
    {
        $this->setExpectedException(\PjbServer\Tools\Exception\InvalidArgumentException::class);
        $params = [
            'pid_file' => '/invalid_path/resources/pjb621_standalone/var/run/server.pid'
        ];
        $config = new Config($params);
    }



    public function testGetConfig()
    {
        $config = PjbServerTestConfig::getStandaloneServerConfig()->getConfig();

        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('port', $config);
        $this->assertArrayHasKey('java_bin', $config);
        $this->assertArrayHasKey('log_file', $config);
        $this->assertArrayHasKey('pid_file', $config);
    }
}
