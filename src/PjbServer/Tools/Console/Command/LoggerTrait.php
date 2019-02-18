<?php

declare(strict_types=1);

namespace PjbServer\Tools\Console\Command;

use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use PjbServer\Tools\StandaloneServer\Config;

trait LoggerTrait
{
    public function getConsoleLogger(OutputInterface $output): ConsoleLogger
    {
        return new ConsoleLogger($output);
    }

    /**
     * @param ConsoleLogger $logger
     * @param Config        $config
     */
    public function logServerConfig(ConsoleLogger $logger, Config $config): void
    {
        $logger->info('* config port       :' . $config->getPort());
        $logger->info('* config log_file   :' . $config->getLogFile());
        $logger->info('* config pid_file   :' . $config->getPidFile());
        $logger->info('* config classpaths :' . implode(',', $config->getClasspaths()));
        $logger->info('* config java_bin   :' . $config->getJavaBin());
        $logger->info('* config server_jar :' . $config->getServerJar());
    }
}
