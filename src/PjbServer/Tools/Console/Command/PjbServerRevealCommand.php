<?php

namespace PjbServer\Tools\Console\Command;

use PjbServer\Tools\StandaloneServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;



class PjbServerRevealCommand extends Command
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
        $this->setName('pjbserver:reveal')
             ->setDescription(
                 'Print the underlying java cli command'
               )
            ->addArgument(
                'config-file',
                InputArgument::REQUIRED,
                'Configuration file, see ./dist/pjbserver.config.php.dist'
            )

             ->setHelp(<<<EOT
Echo the underlying cli command (call to java) that will be called.
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

        $config = new StandaloneServer\Config($params);
        $this->logServerConfig($logger, $config);
        $this->server = new StandaloneServer($config, $logger);

        $output->writeln($this->server->getCommand());

        return 1;
    }
}
