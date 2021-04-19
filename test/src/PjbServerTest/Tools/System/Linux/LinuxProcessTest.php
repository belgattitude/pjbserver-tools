<?php

namespace PjbServerTest\Tools\System\Linux;

use PHPUnit\Framework\TestCase;
use PjbServer\Tools\System\ProcessInterface;
use PjbServer\Tools\System\Linux\LinuxProcess;

class LinuxProcessTest extends TestCase
{
    /**
     * @var LinuxProcess
     */
    protected $process;

    protected function setUp(): void
    {
        $this->process = new LinuxProcess();
    }

    public function testImplementsProcessInterface()
    {
        self::assertInstanceOf(ProcessInterface::class, $this->process);
    }

    public function testIsRunning()
    {
        $crazy_pid = 1239883477;
        self::assertFalse($this->process->isRunning($crazy_pid));
        self::assertIsBool($this->process->isRunning($crazy_pid));

        $my_pid = getmypid();
        self::assertTrue($this->process->isRunning($my_pid));
        self::assertIsBool($this->process->isRunning($my_pid));
    }

    public function testKill()
    {
        $crazy_pid = 1239883477;
        $return = $this->process->kill($crazy_pid);
        self::assertIsBool($return);
        self::assertFalse($return);
    }
}
