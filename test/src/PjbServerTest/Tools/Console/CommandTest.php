<?php

namespace PjbServerTest\Tools\Console;

use PjbServerTestConfig;
use PjbServer\Tools\Console\CommandRepository;
use PjbServer\Tools\StandaloneServer\Config;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandRepository
     */
    protected $commandRepo;

    /**
     * @var Config
     */
    protected $config;

    protected function setUp()
    {
        $this->commandRepo = new CommandRepository();
        $this->config = PjbServerTestConfig::getStandaloneServerConfig();
    }

    public function testServerStopWhileNotRunning()
    {
        $app = new Application();
        $app->add($this->commandRepo->getRegisteredCommand('pjbserver:stop'));
        $command = $app->find('pjbserver:stop');

        $tester = new CommandTester($command);

        $port = $this->config->getPort();
        $tester->execute([
            'config-file' => PjbServerTestConfig::getBaseDir() . '/config/pjbserver.config.php.dist'
        ]);
        
        $this->assertEquals(0, $tester->getStatusCode());

        $this->assertRegexp("/Server running on port $port successfully stopped/", $tester->getDisplay());
    }


    public function testServerStart()
    {
        $app = new Application();
        $app->add($this->commandRepo->getRegisteredCommand('pjbserver:start'));
        $command = $app->find('pjbserver:start');

        $tester = new CommandTester($command);

        $port = $this->config->getPort();
        $tester->execute([
            'config-file' => PjbServerTestConfig::getBaseDir() . '/config/pjbserver.config.php.dist'
        ]);

        $this->assertEquals(0, $tester->getStatusCode());

        $this->assertRegexp("/Server successfully started on port $port/", $tester->getDisplay());
    }


    public function testServerRestart()
    {
        $app = new Application();
        $app->add($this->commandRepo->getRegisteredCommand('pjbserver:restart'));
        $command = $app->find('pjbserver:restart');

        $tester = new CommandTester($command);

        $port = $this->config->getPort();
        $tester->execute([
            'config-file' => PjbServerTestConfig::getBaseDir() . '/config/pjbserver.config.php.dist'
        ]);

        $this->assertEquals(0, $tester->getStatusCode());

        $this->assertRegexp("/Server successfully restarted on port $port/", $tester->getDisplay());
    }

    public function testServerStop()
    {
        $app = new Application();
        $app->add($this->commandRepo->getRegisteredCommand('pjbserver:stop'));
        $command = $app->find('pjbserver:stop');

        $tester = new CommandTester($command);

        $port = $this->config->getPort();
        $tester->execute([
            'config-file' => PjbServerTestConfig::getBaseDir() . '/config/pjbserver.config.php.dist'
        ]);

        $this->assertEquals(0, $tester->getStatusCode());

        $this->assertRegexp("/Server running on port $port successfully stopped/", $tester->getDisplay());
    }
}
