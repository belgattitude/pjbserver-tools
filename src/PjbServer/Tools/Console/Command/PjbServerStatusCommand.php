<?php

namespace PjbServer\Tools\Console\Command;

use PjbServer\Tools\Exception as PjbException;
use PjbServer\Tools\StandaloneServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;



class PjbServerStatusCommand extends Command
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
        $this->setName('pjbserver:status')
             ->setDescription(
                 'Get the status of the standalone pjb server (java)'
               )
            ->addArgument(
                'config-file',
                InputArgument::REQUIRED,
                'Configuration file, see ./dist/pjbserver.config.php.dist'
            )

             ->setHelp(<<<EOT
Get the status of the php java bridge server in the background.
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
        $this->logServerConfig($logger, $config);
        $this->server = new StandaloneServer($config, $logger);

        $pid_file = $config->getPidFile();
        $log_file = $config->getLogFile();

        if (file_exists($log_file)) {
            $output->writeln("---------------------------------------------------------");
            $output->writeln("Content of log file ($log_file)");
            $output->writeln("---------------------------------------------------------");
            $output->writeln($this->server->getOutput());
            $output->writeln("---------------------------------------------------------");
        }


        $isRunning = false;
        try {
            $pid = $this->server->getPid();

            if (!$this->server->isProcessRunning()) {
                $logger->error("Not running but pid file exists ($pid_file) and pid found ($pid)");
                $msg = "Server not running but pid exists (pid: $pid) in pid_file ($pid_file). Please restart.";
            } else {
                $isRunning = true;
                $msg = "Server is running on port '$port' (pid: $pid)";
            }
        } catch (PjbException\PidCorruptedException $e) {
            // Pid file corrupted
            $logger->critical("Cannot find server pid, your '$pid_file' is corrupted. Remove it.");
            $msg = "Server not running (Critical error: pid file corrupted)";
        } catch (PjbException\PidNotFoundException $e) {
            $logger->info("Pid file '$pid_file' not exists, assuming server is down.");
            $msg = "Server not running on port '$port' (no pid file found)";
        } catch (\Exception $e) {
            $logger->error('Unexpected exception when testing pid file.');
            $msg = "Cannot test server status";
        }

        $output->writeln($msg);

        return ($isRunning) ? 0 : 1;
    }
}
