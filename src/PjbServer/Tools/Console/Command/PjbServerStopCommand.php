<?php

namespace PjbServer\Tools\Console\Command;

use PjbServer\Tools\StandaloneServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;



class PjbServerStopCommand extends Command
{
    /**
     * @var StandaloneServer
     */
    protected $server;


    /**
     * {@inheritdoc}
     */
    protected function configure()
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
             ->setHelp(<<<EOT
Stop the standalone php java bridge server (running in the background).
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('config-file');

        // Test if config file exists
        if (!file_exists($file) || !is_readable($file)) {
            $msg = "Configuration file '$file' does not exists or is not readable'";
            throw new \InvalidArgumentException($msg);
        }
        $params = include($file);
        $port = $params['port'];

        $config = new StandaloneServer\Config($params);
        $this->server = new StandaloneServer($config);

        $this->server->stop();

        $output->write("Server running on port $port successfully stopped" . PHP_EOL);
    }
}
