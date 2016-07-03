<?php

namespace PjbServer\Tools\StandaloneServer;

use PjbServer\Tools\Exception;

/*
  java -Djava.awt.headless="true"
  -Dphp.java.bridge.threads=50
  -Dphp.java.bridge.base=/usr/lib/php/modules
  -Dphp.java.bridge.php_exec=/usr/local/bin/php-cgi
  -Dphp.java.bridge.default_log_file=
  -Dphp.java.bridge.default_log_level=5
  -Dphp.java.bridge.daemon="false"
  -jar JavaBridge.jar
 * sudo netstat -anltp|grep :8089
 */

class Config
{

    /**
     * Default configuration options
     * @var array
     */
    protected $default_config = [
        'java_bin'   => 'java',
        'server_jar' => '{base_dir}/resources/pjb621_standalone/JavaBridge.jar',
        'log_file'   => '{base_dir}/var/pjbserver-port{tcp_port}.log',
        'pid_file'   => '{base_dir}/var/pjbserver-port{tcp_port}.pid',
    ];

    /**
     * Internal configuration array
     * @var array
     */
    protected $config;


    /**
     * Constructor
     *
     * <code>
     *
     * $params = [
     *      // required
     *      'port' => 8089,
     *
     *      // optionally
     *      'java_bin'   => 'java',
     *      'server_jar' => '{base_dir}/resources/pjb621_standalone/JavaBridge.jar',
     *      'log_file'   => '{base_dir}/var/pjbserver-port{tcp_port}.log',
     *      'pid_file'   => '{base_dir}/var/pjbserver-port{tcp_port}.pid',
     * ];
     * $config = new StandaloneServer\Config($params);
     * </code>
     *
     * @throws Exception\InvalidArgumentException
     * @param array $config
     * @param LoggerInterface $logger
     *
     */
    public function __construct(array $config)
    {
        if (!isset($config['port'])) {
            throw new Exception\InvalidArgumentException("Error missing required 'port' in config");
        } elseif (!filter_var($config['port'], FILTER_VALIDATE_INT) || $config['port'] < 1) {
            throw new Exception\InvalidArgumentException("Option 'port' must be numeric greater than 0");
        }
        $config = array_merge($this->getDefaultConfig($config['port']), $config);
        $this->checkConfig($config);
        $this->config = $config;
    }

    /**
     * Return port on which standalone server listens
     * @return int
     */
    public function getPort()
    {
        return $this->config['port'];
    }

    /**
     * Return jar file of the server
     * @return string
     */
    public function getServerJar()
    {
        return $this->config['server_jar'];
    }


    /**
     * Return java binary
     * @return string
     */
    public function getJavaBin()
    {
        return $this->config['java_bin'];
    }

    /**
     * Return log file
     * @return string
     */
    public function getLogFile()
    {
        return $this->config['log_file'];
    }

    /**
     *
     * @return string
     */
    public function getClasspaths()
    {
        return $this->config['classpaths'];
    }

    /**
     * Return pid file where to store process id
     * @return string
     */
    public function getPidFile()
    {
        return $this->config['pid_file'];
    }

    /**
     * Return standalone configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Return default configuration options
     * @param int $port
     * @return array
     */
    protected function getDefaultConfig($port)
    {
        $base_dir = realpath(__DIR__ . '/../../../../');
        $config = [];
        foreach ($this->default_config as $key => $value) {
            $tmp = str_replace('{base_dir}', $base_dir, $value);
            $tmp = str_replace('{tcp_port}', $port, $tmp);
            $config[$key] = $tmp;
        }
        return $config;
    }

    /**
     * Check configuration parameters
     * @throws Exception\InvalidArgumentException
     * @param array $config
     */
    protected function checkConfig(array $config)
    {
        // Step 1: all required options
        $required = ['port', 'server_jar', 'log_file', 'pid_file'];
        foreach ($required as $option) {
            if (!isset($config[$option]) || $config[$option] == '') {
                throw new Exception\InvalidArgumentException("Missing resuired configuration option: '$option''");
            }
        }

        // Step 2: server_jar file must exists
        if (!is_file($config['server_jar']) || !is_readable($config['server_jar'])) {
            throw new Exception\InvalidArgumentException("Server jar file not exists or unreadable. server-jar: '" . $config['server_jar'] ."'");
        }

        // Step 3: log and pid file should be creatable
        $temp_required_files = ['log_file', 'pid_file'];
        foreach ($temp_required_files as $option) {
            $file = $config[$option];
            $info = pathinfo($file);
            $dirname = $info['dirname'];
            if (!is_dir($dirname) || $dirname == ".") {
                $msg = "Option '$option' refer to an invalid or non-existent directory ($file)";
                throw new Exception\InvalidArgumentException($msg);
            }
            if (is_dir($file)) {
                $msg = "Option '$option' does not refer to a file but an existing directory ($file)";
                throw new Exception\InvalidArgumentException($msg);
            }
            if (file_exists($file) && !is_writable($file)) {
                $msg = "File specified in '$option' is not writable ($file)";
                throw new Exception\InvalidArgumentException($msg);
            }
        }

        // Step 4: Java must be callable

        // Step 5: Check autoload
    }
}
