<?php

namespace PjbServerTest\Functionnal;

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;
use PjbServer\Tools\StandaloneServer;
use PjbServerTestConfig;
use Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient;

class BridgeConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StandaloneServer
     */
    protected $server;

    protected function setUp()
    {
        $config = PjbServerTestConfig::getStandaloneServerConfig();
        $this->server = new StandaloneServer($config);
        $this->server->start();
    }

    protected function tearDown()
    {
        $this->server->stop($throws_exception = false);
        PjbProxyClient::unregisterInstance();
    }

    public function testBasicUsage()
    {
        $config = PjbServerTestConfig::getStandaloneServerConfig();
        $port = $config->getPort();

        $ba = new BridgeAdapter([
            'driver' => 'Pjb62',
            'servlet_address' => "localhost:$port/MyJavaBridge/servlet.phpjavabridge"
        ]);

        $jString = $ba->java('java.lang.String', 'hello');
        $this->assertEquals('hello', (string) $jString);
        //die();
    }
}
