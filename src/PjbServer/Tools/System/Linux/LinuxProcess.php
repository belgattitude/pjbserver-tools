<?php

declare(strict_types=1);

namespace PjbServer\Tools\System\Linux;

use PjbServer\Tools\Exception;
use PjbServer\Tools\System\ProcessInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class LinuxProcess implements ProcessInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * LinuxProcess constructor.
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        if ($logger === null) {
            $logger = new NullLogger();
        }
        $this->logger = $logger;
    }

    /**
     * Check whether a pid is running.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param int $pid
     *
     * @return bool
     */
    public function isRunning(int $pid): bool
    {
        $cmd = sprintf('kill -0 %d 2>&1', $pid);
        $this->logger->debug(__METHOD__ . ": Exec command: $cmd");
        exec($cmd, $output, $return_var);

        return $return_var === 0;
    }

    /**
     * Kill a process.
     *
     * @throws Exception\InvalidArgumentException
     */
    public function kill(int $pid, bool $wait = false): bool
    {
        $killed = false;
        if ($this->isRunning($pid)) {
            if ($wait) {
                $sleep_time = '0.2';
                $cmd = sprintf('kill %d; while ps -p %d; do sleep %s;done;', $pid, $pid, $sleep_time);
                $this->logger->debug(__METHOD__ . " Exec command: $cmd");
                exec($cmd, $output, $return_var);
                $killed = ($return_var === 0);
                if ($killed) {
                    $this->logger->debug(__METHOD__ . " Successfully killed process {$pid}");
                } else {
                    $this->logger->notice(__METHOD__ . " Cannot kill process {$pid}, $output");
                }
            } else {
                //@todo
                throw new \Exception('@todo Only wait=true is supported for now');
            }
        }

        return $killed;
    }
}
