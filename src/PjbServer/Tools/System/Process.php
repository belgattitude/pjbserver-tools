<?php

declare(strict_types=1);

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
     *
     * @param string               $style
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $style = self::LINUX_STYLE, LoggerInterface $logger = null)
    {
        if ($logger === null) {
            $logger = new NullLogger();
        }
        $this->process = $this->getProcess($style, $logger);
        $this->logger = $logger;
    }

    /**
     * Create "internal styled" process.
     *
     * @param string          $style
     * @param LoggerInterface $logger
     *
     * @throws UnsupportedSystemException
     */
    protected function getProcess(string $style, LoggerInterface $logger): ProcessInterface
    {
        switch ($style) {
            case self::LINUX_STYLE:
                $process = new Linux\LinuxProcess($logger);
                break;
            default:
                $msg = "System style '" . $style . "' is not supported";
                throw new UnsupportedSystemException($msg);
        }

        return $process;
    }

    /**
     * Check whether a pid is running.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param int $pid
     */
    public function isRunning(int $pid): bool
    {
        return $this->process->isRunning($pid);
    }

    /**
     * Kill a process.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param int  $pid
     * @param bool $wait
     *
     * @return bool
     */
    public function kill(int $pid, bool $wait = false): bool
    {
        return $this->process->kill($pid, $wait);
    }
}
