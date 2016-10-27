<?php

namespace PjbServerTest\Tools\System\Linux;

use PjbServer\Tools\System\ProcessInterface;
use PjbServer\Tools\System\Linux\LinuxProcess;

class LinuxProcessTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var LinuxProcess
     */
    protected $process;

    protected function setUp()
    {
        $this->process = new LinuxProcess();
    }

    protected function tearDown()
    {
    }

    public function testImplementsProcessInterface()
    {
        $this->assertInstanceOf(ProcessInterface::class, $this->process);
    }

    public function testIsRunning()
    {
        $crazy_pid = 1239883477;
        $this->assertFalse($this->process->isRunning($crazy_pid));
        $this->assertInternalType('boolean', $this->process->isRunning($crazy_pid));

        $my_pid = getmypid();
        $this->assertTrue($this->process->isRunning($my_pid));
        $this->assertInternalType('boolean', $this->process->isRunning($my_pid));
    }

    public function testKill()
    {
        $crazy_pid = 1239883477;
        $return = $this->process->kill($crazy_pid);
        $this->assertInternalType('boolean', $return);
        $this->assertFalse($return);
    }
}
