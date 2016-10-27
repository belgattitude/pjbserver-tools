<?php

namespace PjbServerTest\Tools\System;

use PjbServer\Tools\System;
use PjbServer\Tools\Exception;

class ProcessTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var System\Linux\LinuxProcess
     */
    protected $linuxProcess;

    protected function setUp()
    {
        $this->linuxProcess = new System\Process(System\Process::LINUX_STYLE);
    }

    protected function tearDown()
    {
    }

    public function testUnsupportedSystem()
    {
        $this->setExpectedException(Exception\UnsupportedSystemException::class);
        $process = new System\Process('WinMac');
    }


    public function testImplementsProcessInterface()
    {
        $process = new System\Process();
        $this->assertInstanceOf(System\ProcessInterface::class, $process);
    }

    public function testIsRunning()
    {
        $crazy_pid = 1239883477;
        $this->assertFalse($this->linuxProcess->isRunning($crazy_pid));
        $this->assertInternalType('boolean', $this->linuxProcess->isRunning($crazy_pid));

        $my_pid = getmypid();
        $this->assertTrue($this->linuxProcess->isRunning($my_pid));
        $this->assertInternalType('boolean', $this->linuxProcess->isRunning($my_pid));
    }
}
