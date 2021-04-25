<?php

namespace PjbServerTest\Functionnal;

use PHPUnit\Framework\TestCase;
use Soluble\Japha\Bridge\Adapter as BridgeAdapter;
use PjbServer\Tools\StandaloneServer;
use PjbServerTestConfig;
use Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient;

class BridgeConnectionTest extends TestCase
{
    /**
     * @var StandaloneServer
     */
    protected $server;

    protected function setUp(): void
    {
        $config = PjbServerTestConfig::getStandaloneServerConfig();
        $this->server = new StandaloneServer($config);
        $this->server->start();
    }

    protected function tearDown(): void
    {
        $this->server->stop($throws_exception = false);
        PjbProxyClient::unregisterInstance();
    }

    public function testBasicUsage(): void
    {
        $config = PjbServerTestConfig::getStandaloneServerConfig();
        $port = $config->getPort();

        $ba = new BridgeAdapter([
            'driver' => 'Pjb62',
            'servlet_address' => "localhost:$port/MyJavaBridge/servlet.phpjavabridge"
        ]);

        $jString = $ba->java('java.lang.String', 'hello');
        self::assertEquals('hello', (string) $jString);
    }
}
