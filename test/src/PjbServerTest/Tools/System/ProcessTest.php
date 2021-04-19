<?php

namespace PjbServerTest\Tools\System;

use PHPUnit\Framework\TestCase;
use PjbServer\Tools\System;
use PjbServer\Tools\Exception;

class ProcessTest extends TestCase
{
    /**
     * @var System\Linux\LinuxProcess
     */
    protected $linuxProcess;

    protected function setUp(): void
    {
        $this->linuxProcess = new System\Process(System\Process::LINUX_STYLE);
    }

    public function testUnsupportedSystem()
    {
        self::expectException(Exception\UnsupportedSystemException::class);
        $process = new System\Process('WinMac');
    }

    public function testImplementsProcessInterface()
    {
        $process = new System\Process();
        self::assertInstanceOf(System\ProcessInterface::class, $process);
    }

    public function testIsRunning()
    {
        $crazy_pid = 1239883477;
        self::assertFalse($this->linuxProcess->isRunning($crazy_pid));
        self::assertIsBool($this->linuxProcess->isRunning($crazy_pid));

        $my_pid = getmypid();
        self::assertTrue($this->linuxProcess->isRunning($my_pid));
        self::assertIsBool($this->linuxProcess->isRunning($my_pid));
    }
}
