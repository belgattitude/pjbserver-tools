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

    public function testInvalidClassPath()
    {
        $config = PjbServerTestConfig::getStandaloneServerConfig()->getConfig();
        try {
            $config['classpaths'] = 'cool';
            $cfg = new Config($config);
            $this->assertFalse(true, "Exception should be thrown when passing an invalid classpaths option");
        } catch (\PjbServer\Tools\Exception\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        try {
            $config['classpaths'] = [
                '/invalidfile'
            ];
            $cfg = new Config($config);
            $this->assertFalse(true, "Exception should be thrown when passing a classpath option not finishing by .jar");
        } catch (\PjbServer\Tools\Exception\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        try {
            $config['classpaths'] = [
                '/unexisting/test.jar'
            ];
            $cfg = new Config($config);
            $this->assertFalse(true, "Exception should be thrown when passing a classpath option with file not existing");
        } catch (\PjbServer\Tools\Exception\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        try {
            $config['classpaths'] = [
                '/invalid_dir/*.jar'
            ];
            $cfg = new Config($config);
            $this->assertFalse(true, "Exception should be thrown when passing a classpath option with *.jar in an unexisting dir");
        } catch (\PjbServer\Tools\Exception\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetBaseDir()
    {
        $config = PjbServerTestConfig::getStandaloneServerConfig();
        $base_dir = $config->getBaseDir();
        $this->assertNotEmpty($base_dir);
        $this->assertTrue(is_dir($base_dir));
    }


    public function testInvalidThreads()
    {
        $config = PjbServerTestConfig::getStandaloneServerConfig()->getConfig();
        try {
            $config['threads'] = 'A';
            $cfg = new Config($config);
            $this->assertFalse(true, "Exception should be thrown when passing invalid threads option.");
        } catch (\PjbServer\Tools\Exception\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testThreadsDefault()
    {
        $config = PjbServerTestConfig::getStandaloneServerConfig()->getConfig();
        unset($config['threads']);
        $cfg = new Config($config);
        $this->assertEquals(50, $cfg->getThreads());
    }


    public function testGetConfig()
    {
        $config = PjbServerTestConfig::getStandaloneServerConfig()->getConfig();

        $this->assertInternalType('array', $config);
        $this->assertArrayHasKey('port', $config);
        $this->assertArrayHasKey('java_bin', $config);
        $this->assertArrayHasKey('log_file', $config);
        $this->assertArrayHasKey('pid_file', $config);
        $this->assertArrayHasKey('threads', $config);
    }
}
