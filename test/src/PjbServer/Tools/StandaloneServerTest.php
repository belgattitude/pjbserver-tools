<?php

namespace PjbServer\Tools;

class StandaloneServerTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
    }

    protected function tearDown()
    {
    }

    public function testConstructorThrowsInvalidArgumentException()
    {
        $this->setExpectedException('PjbServer\Tools\Exception\InvalidArgumentException');
        $config = array();
        $server = new StandaloneServer($config);
    }
    
    public function testConstructorThrowsInvalidArgumentException2()
    {
        $this->setExpectedException('PjbServer\Tools\Exception\InvalidArgumentException');
        $config = array('server_port' => 'cool');
        $server = new StandaloneServer($config);
    }
    
}
