<?php

namespace PjbServer\Tools\System;

use PjbServer\Tools\Exception;
use PjbServer\Tools\Exception\UnsupportedSystemException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;



class Process implements ProcessInterface
{
    const LINUX_STYLE = 'linux';

    /**
     * @var ProcessInterface
     */
    protected $process;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Process constructor.
     * @param string $style
     * @param LoggerInterface|null $logger
     */
    public function __construct($style = self::LINUX_STYLE, LoggerInterface $logger = null)
    {
        if ($logger === null) {
            $logger = new NullLogger();
        }
        $this->process = $this->getProcess($style, $logger);
        $this->logger = $logger;
    }

    /**
     * Create "internal styled" process
     *
     * @param string $style
     * @param LoggerInterface $logger
     * @return ProcessInterface
     */
    protected function getProcess($style, LoggerInterface $logger)
    {
        switch ($style) {
            case self::LINUX_STYLE:
                $process = new Linux\LinuxProcess($logger);
                break;
            default:
                $msg = "System style '" . (string) $style . "' is not supported";
                throw new UnsupportedSystemException($msg);
        }
        return $process;
    }

    /**
     * Check whether a pid is running
     *
     * @throws Exception\InvalidArgumentException
     * @param int $pid
     * @return boolean
     */
    public function isRunning($pid)
    {
        return $this->process->isRunning($pid);
    }

    /**
     * Kill a process
     *
     * @throws Exception\InvalidArgumentException
     * @param int $pid
     * @param bool $wait
     * @return boolean
     */
    public function kill($pid, $wait = false)
    {
        return $this->process->kill($pid, $wait);
    }
}
