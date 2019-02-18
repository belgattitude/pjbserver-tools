<?php

declare(strict_types=1);

namespace PjbServer\Tools\Console\Command;

use PjbServer\Tools\StandaloneServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PjbServerStopCommand extends Command
{
    use LoggerTrait;

    /**
     * @var StandaloneServer
     */
    protected $server;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('pjbserver:stop')
             ->setDescription(
                 'Stop the standalone pjb server (java)'
               )
            ->addArgument(
                'config-file',
                InputArgument::REQUIRED,
                'Configuration file, see ./dist/pjbserver.config.php.dist'
            )
             ->setHelp(
                 <<<'EOT'
Stop the standalone php java bridge server (running in the background).
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger = $this->getConsoleLogger($output);
        $file = $input->getArgument('config-file');

        // Test if config file exists
        if (!file_exists($file) || !is_readable($file)) {
            $msg = "Configuration file '$file' does not exists or is not readable'";
            throw new \InvalidArgumentException($msg);
        }
        $params = include $file;
        $port = $params['port'];

        $config = new StandaloneServer\Config($params);

        $logger->notice("Stopping the server on port '$port' and config file '$file'");
        $this->logServerConfig($logger, $config);

        $this->server = new StandaloneServer($config, $logger);

        $pid_file = $this->server->getConfig()->getPidFile();
        if (!file_exists($pid_file)) {
            $output->writeln("Server already stopped (pid_file '${pid_file}' not found).");
        } else {
            $this->server->stop();
            $output->writeln("Server running on port $port successfully stopped");
        }

        return 0;
    }
}
