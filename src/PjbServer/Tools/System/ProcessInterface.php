<?php

declare(strict_types=1);

namespace PjbServer\Tools\System;

use PjbServer\Tools\Exception;

interface ProcessInterface
{
    /**
     * Kill a process.
     *
     * @throws Exception\InvalidArgumentException
     */
    public function kill(int $pid, bool $wait = false): bool;

    /**
     * Check whether a pid is running.
     *
     * @throws Exception\InvalidArgumentException
     */
    public function isRunning(int $pid): bool;
}
