<?php

namespace PjbServer\Tools\Network;

class PortTester
{
    const BACKEND_STREAM_SOCKET = 'stream_socket';
    const BACKEND_SOCKET_CREATE = 'socket_create';
    const BACKEND_PFSOCKOPEN = 'pfsockopen';
    const BACKEND_CURL = 'curl';


    const PROTOCOL_TCP = 'tcp';
    const PROTOCOL_UDP = 'udp';
    const PROTOCOL_HTTP = 'http';
    const PROTOCOL_HTTPS = 'https';

    /**
     * @var array
     */
    protected $supportedBackends = ['stream_socket', 'socket_create', 'pfsockopen', 'curl'];

    /**
     * @var array
     */
    protected $supportedProtocols = ['tcp', 'udp', 'http', 'https'];


    /**
     * @var array
     */
    protected $defaults = [
        'backend' => null,
        'timeout' => 1,
        'close_timeout_ms' => null
    ];


    /**
     *
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * <code>
     * $options = [
     *      'backend' => PortTester::BACKEND_STREAM_SOCKET,
     *      // connection timeout in seconds
     *      'timeout' => 1,
     *      // timeout to wait for connection to be closed
     *      // properly in milliseconds or null to disable
     *      // Use when TIME_WAIT is too long
     *      'close_timeout_ms => 300
     * ];
     * $portTester = new PortTester($options);
     * </code>
     *
     * @throws \InvalidArgumentException
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->options = array_merge($this->defaults, $options);
        if ($this->options['backend'] == '') {
            if ($this->isCurlAvailable()) {
                $this->options['backend'] = self::BACKEND_CURL;
            } else {
                $this->options['backend'] = self::BACKEND_STREAM_SOCKET;
            }
        }
        if (!in_array($this->options['backend'], $this->supportedBackends)) {
            throw new \InvalidArgumentException("Unsupported backend '" . $this->options['backend'] . "'");
        }
    }

    /**
     * Check if TCP port is available for binding
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @param string $host
     * @param int $port
     * @param string $protocol
     * @param int|null $timeout
     * @return boolean
     */
    public function isAvailable($host, $port, $protocol = 'http', $timeout = null)
    {
        if (!in_array($protocol, $this->supportedProtocols)) {
            throw new \InvalidArgumentException("Unsupported protocol '$protocol'");
        }

        if ($timeout === null) {
            $timeout = $this->options['timeout'];
        }

        $available = false;
        $sock = null;

        $backend = $this->options['backend'];
        switch ($backend) {
            case self::BACKEND_PFSOCKOPEN:
                $sock = @pfsockopen("$protocol://$host", $port, $errno, $errstr, $timeout);
                if (!$sock) {
                    $available = true;
                } else {
                    fclose($sock);
                }
                break;

            case self::BACKEND_SOCKET_CREATE:
                $timeout = 0;
                $protocolMap = ['tcp' => SOL_TCP, 'udp' => SOL_UDP];
                if (!array_key_exists($protocol, $protocolMap)) {
                    throw new \RuntimeException("Backedn socket_create does not support protocol $protocol");
                }
                $proto = $protocolMap[$protocol];

                $sock = socket_create(AF_INET, SOCK_STREAM, $proto);
                socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $timeout, 'usec' => 0]);
                if (!@socket_connect($sock, $host, $port)) {
                    $available = true;
                } else {
                    socket_close($sock);
                }
                break;

            case self::BACKEND_STREAM_SOCKET:
                $flags = STREAM_CLIENT_CONNECT; //  & ~STREAM_CLIENT_PERSISTENT
                $sock = @stream_socket_client("$protocol://$host:$port", $errno, $errstr, $timeout, $flags);
                if (!$sock) {
                    $available = true;
                } else {
                    if (!stream_socket_shutdown($sock, STREAM_SHUT_RDWR)) {
                        throw new \RuntimeException("Cannot properly close socket stream.");
                    }
                    fclose($sock);
                    //fclose($sock);
                    //unset($sock);
                }
                break;

            case 'curl':
                if (!$this->isCurlAvailable()) {
                    throw new \RuntimeException("Curl not available");
                }
                $curl_options = [
                    CURLOPT_URL => "http://$host:$port",
                    CURLOPT_TIMEOUT => $timeout,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FAILONERROR => true,
                    CURLOPT_PORT => $port,
                ];

                $curl_handle = curl_init();
                curl_setopt_array($curl_handle, $curl_options);
                curl_exec($curl_handle);
                $errno = curl_errno($curl_handle);
                if ($errno != 0) {
                    $available = true;
                    //echo curl_error($curl_handle);
                }
                curl_close($curl_handle);
                break;
            default:
                throw new \InvalidArgumentException("Unsupported backend: '$backend'.");
        }
        unset($sock);
        if ($this->options['close_timeout_ms'] > 0) {
            usleep($this->options['close_timeout_ms'] * 1000);
        }
        return $available;
    }


    /**
     * Test whether curl extension is available
     * @return boolean
     */
    protected function isCurlAvailable()
    {
        return function_exists('curl_version');
    }
}
