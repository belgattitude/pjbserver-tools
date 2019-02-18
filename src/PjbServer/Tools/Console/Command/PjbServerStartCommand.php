<?php

declare(strict_types=1);

namespace PjbServer\Tools\Console\Command;

use PjbServer\Tools\StandaloneServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PjbServerStartCommand extends Command
{
    use LoggerTrait;

    /**
     * @var StandaloneServer
     */
    protected $server;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pjbserver:start')
             ->setDescription(
                 'Start the standalone pjb server (java)'
               )
            ->addArgument(
                'config-file',
                InputArgument::REQUIRED,
                'Configuration file, see ./dist/pjbserver.config.php.dist'
            )

             ->setHelp(
                 <<<'EOT'
Start the php java bridge server in the background.
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
        $logger->notice("Starting the server on port '$port' and config file '$file'");
        $this->logServerConfig($logger, $config);
        $this->server = new StandaloneServer($config, $logger);

        if ($this->server->isProcessRunning()) {
            $pid = $this->server->getPid();
            $output->writeln("PjbServer is already running on port ${port} with pid {$pid}, skipping start");
        } else {
            $this->server->start();
            $output->writeln("Server successfully started on port $port");
        }

        return 0;
    }
}
