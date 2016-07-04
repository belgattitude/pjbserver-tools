<?php

namespace PjbServer\Tools\Console\Command;

use PjbServer\Tools\StandaloneServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PjbServerRestartCommand extends Command
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
        $this->setName('pjbserver:restart')
             ->setDescription(
                 'Restart the standalone pjb server (java)'
               )
             ->addArgument(
                'config-file',
                InputArgument::REQUIRED,
                'Configuration file, see ./dist/pjbserver.config.php.dist'
               )
             ->setHelp(<<<EOT
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
        $params = include($file);
        $port = $params['port'];
        $config = new StandaloneServer\Config($params);

        $logger->notice("PJB server using port '$port' and config in '$file'");
        $this->logServerConfig($logger, $config);

        $this->server = new StandaloneServer($config, $logger);
        $this->server->restart();

        $logger->debug("Server output: \n" . $this->server->getOutput());

        $output->writeln("Server successfully restarted on port $port");
        return 0;
    }
}
