<?php

namespace PjbServerTest\Tools\Console;

use PHPUnit\Framework\TestCase;
use PjbServerTestConfig;
use PjbServer\Tools\Console\CommandRepository;
use PjbServer\Tools\StandaloneServer\Config;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CommandTest extends TestCase
{
    /**
     * @var CommandRepository
     */
    protected $commandRepo;

    /**
     * @var Config
     */
    protected $config;

    protected function setUp(): void
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

        self::assertEquals(0, $tester->getStatusCode());

        $pid_file = $this->config->getPidFile();

        if (!file_exists($pid_file)) {
            self::assertRegexp("/Server already stopped \(pid_file (.*) not found\)./", $tester->getDisplay());
        } else {
            self::assertRegexp("/Server running on port $port successfully stopped/", $tester->getDisplay());
        }
    }

    public function testServerStatusWhileStopped()
    {
        $app = new Application();
        $app->add($this->commandRepo->getRegisteredCommand('pjbserver:status'));
        $command = $app->find('pjbserver:status');

        $tester = new CommandTester($command);

        $port = $this->config->getPort();
        $tester->execute([
            'config-file' => PjbServerTestConfig::getBaseDir() . '/config/pjbserver.config.php.dist'
        ]);

        self::assertEquals(1, $tester->getStatusCode());

        self::assertMatchesRegularExpression("/Server not running on port '$port' \(no pid file found\)/", $tester->getDisplay());
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

        self::assertEquals(0, $tester->getStatusCode());

        self::assertRegexp("/Server successfully started on port $port/", $tester->getDisplay());
    }

    public function testServerStatusWhileRunning()
    {
        $app = new Application();
        $app->add($this->commandRepo->getRegisteredCommand('pjbserver:status'));
        $command = $app->find('pjbserver:status');

        $tester = new CommandTester($command);

        $port = $this->config->getPort();
        $tester->execute([
            'config-file' => PjbServerTestConfig::getBaseDir() . '/config/pjbserver.config.php.dist'
        ]);

        self::assertEquals(0, $tester->getStatusCode());

        self::assertRegexp("/Server is running on port '$port'/", $tester->getDisplay());
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

        self::assertEquals(0, $tester->getStatusCode());

        self::assertRegexp("/Server successfully restarted on port $port/", $tester->getDisplay());
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

        self::assertEquals(0, $tester->getStatusCode());

        self::assertRegexp("/Server running on port $port successfully stopped/", $tester->getDisplay());
    }

    public function testReveal()
    {
        $app = new Application();
        $app->add($this->commandRepo->getRegisteredCommand('pjbserver:reveal'));
        $command = $app->find('pjbserver:reveal');

        $tester = new CommandTester($command);

        $port = $this->config->getPort();
        $tester->execute([
            'config-file' => PjbServerTestConfig::getBaseDir() . '/config/pjbserver.config.php.dist'
        ]);

        self::assertEquals(1, $tester->getStatusCode());

        self::assertRegexp("/java -cp(.*)JavaBridge.jar(.*)SERVLET:$port/", $tester->getDisplay());
    }
}
