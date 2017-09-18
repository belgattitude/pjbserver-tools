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
     * Default configuration options.
     *
     * @var array
     */
    protected $default_config = [
        'java_bin' => 'java',
        'server_jar' => '{base_dir}/resources/pjb713_standalone/JavaBridge.jar',
        'log_file' => '{base_dir}/var/pjbserver-port{tcp_port}.log',
        'pid_file' => '{base_dir}/var/pjbserver-port{tcp_port}.pid',
        'classpaths' => [],
        'threads' => 50
    ];

    /**
     * Internal configuration array.
     *
     * @var array
     */
    protected $config;

    /**
     * Constructor.
     *
     * <code>
     *
     * $params = [
     *      // Port (required)
     *      'port' => 8089,
     *
     *      // Classpath autoloads (optional)
     *      'classpaths' => [
     *          '/my/path/to_specific/jar_file.jar',
     *          '/my/path/to_all_jars/*.jar'
     *      ],
     *
     *      'threads' => 50,
     *
     *      // Defaults (optional)
     *      'java_bin'   => 'java',
     *      'server_jar' => '{base_dir}/resources/pjb621_standalone/JavaBridge.jar',
     *      'log_file'   => '{base_dir}/var/pjbserver-port{tcp_port}.log',
     *      'pid_file'   => '{base_dir}/var/pjbserver-port{tcp_port}.pid'
     *
     * ];
     * $config = new StandaloneServer\Config($params);
     * </code>
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (!isset($config['port'])) {
            throw new Exception\InvalidArgumentException("Error missing required 'port' in config");
        } elseif (!filter_var($config['port'], FILTER_VALIDATE_INT) || $config['port'] < 1) {
            throw new Exception\InvalidArgumentException("Option 'port' must be numeric greater than 0");
        }
        $port = $config['port'];
        // Substitute magic vars is deprecated and will be removed in v1.0.0
        $config = array_merge(
                        $this->getDefaultConfig($port),
                        $this->substitutePlaceholders($config, $port)
        );
        $this->checkConfig($config);
        $this->config = $config;
    }

    /**
     * Return port on which standalone server listens.
     *
     * @return int
     */
    public function getPort()
    {
        return $this->config['port'];
    }

    /**
     * Return jar file of the server.
     *
     * @return string
     */
    public function getServerJar()
    {
        return $this->config['server_jar'];
    }

    /**
     * Return java binary.
     *
     * @return string
     */
    public function getJavaBin()
    {
        return $this->config['java_bin'];
    }

    /**
     * Return log file.
     *
     * @return string
     */
    public function getLogFile()
    {
        return $this->config['log_file'];
    }

    /**
     * Return an array containing the java classpath(s) for the server.
     *
     * @return array
     */
    public function getClasspaths()
    {
        return $this->config['classpaths'];
    }

    /**
     * Return pid file where to store process id.
     *
     * @return string
     */
    public function getPidFile()
    {
        return $this->config['pid_file'];
    }

    /**
     * Return standalone server threads.
     *
     * @return int|string
     */
    public function getThreads()
    {
        return $this->config['threads'];
    }

    /**
     * Return standalone configuration.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->config;
    }

    /**
     * Return standalone configuration.
     *
     * @deprecated use toArray() instead
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->toArray();
    }

    /**
     * Return default configuration options.
     *
     * @param int $port
     *
     * @return array
     */
    protected function getDefaultConfig($port)
    {
        return $this->substitutePlaceholders($this->default_config, $port);
    }

    /**
     * Substitute the placeholder {tcp_port} and {base_dir}
     * from a config array.
     *
     * @param array $configArray associative array
     *
     * @return array
     */
    protected function substitutePlaceholders(array $configArray, $port)
    {
        $substituted = [];
        $base_dir = $this->getBaseDir();

        foreach ($configArray as $key => $value) {
            $tmp = str_replace('{base_dir}', $base_dir, $value);
            $tmp = str_replace('{tcp_port}', $port, $tmp);
            $substituted[$key] = $tmp;
        }

        return $substituted;
    }

    /**
     * Return pjbserver-tools installation base directory.
     *
     * @throws Exception\RuntimeException
     *
     * @return string
     */
    public function getBaseDir()
    {
        // Four levels back.
        $ds = DIRECTORY_SEPARATOR;
        $dir = __DIR__ . "$ds..$ds..$ds..$ds..$ds";
        $base_dir = realpath($dir);
        if (!$base_dir) {
            $message = 'Cannot resolve project base directory.';
            throw new Exception\RuntimeException($message);
        }

        return $base_dir;
    }

    /**
     * Check configuration parameters.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param array $config
     */
    protected function checkConfig(array $config)
    {
        // Step 1: all required options
        $required = ['port', 'server_jar', 'log_file', 'pid_file', 'threads'];
        foreach ($required as $option) {
            if (!isset($config[$option]) || $config[$option] == '') {
                throw new Exception\InvalidArgumentException("Missing resuired configuration option: '$option''");
            }
        }

        // Step 2: server_jar file must exists
        if (!is_file($config['server_jar']) || !is_readable($config['server_jar'])) {
            throw new Exception\InvalidArgumentException("Server jar file not exists or unreadable. server-jar: '" . $config['server_jar'] . "'");
        }

        // Step 3: log and pid file should be creatable
        $temp_required_files = ['log_file', 'pid_file'];
        foreach ($temp_required_files as $option) {
            $file = $config[$option];
            $info = pathinfo($file);
            $dirname = $info['dirname'];
            if (!is_dir($dirname) || $dirname == '.') {
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

        // Step 4: Threads must be numeric greater than 0

        $threads = $config['threads'];

        if (!preg_match('/^([0-9])+$/', $threads) || $threads <= 0) {
            $msg = "Parameter 'threads' must be valid integer greater than 0";
            throw new Exception\InvalidArgumentException($msg);
        }

        // Step 5: Java must be callable

        // @todo, many options exists

        // Step 6: Check classpaths autoload
        if (isset($config['classpaths'])) {
            if (!is_array($config['classpaths'])) {
                $msg = "Option 'classpaths' mus be a php array.";
                throw new Exception\InvalidArgumentException($msg);
            }
            foreach ($config['classpaths'] as $classpath) {
                if (preg_match('/\*\.jar$/', $classpath)) {
                    // Check if directory exists
                    $directory = preg_replace('/\*\.jar$/', '', $classpath);
                    if (!is_dir($directory) || !is_readable($directory)) {
                        $msg = "Classpath error, the directory of '$classpath' does not exists or is not readable";
                        throw new Exception\InvalidArgumentException($msg);
                    }
                } elseif (preg_match('/\.jar$/', $classpath)) {
                    // Check if file exists
                    if (!is_file($classpath) || !is_readable($classpath)) {
                        $msg = "Classpath error, the file '$classpath' does not exists or is not readable";
                        throw new Exception\InvalidArgumentException($msg);
                    }
                } else {
                    $msg = "Error in classpath, files to import must end by .jar extension ($classpath)";
                    throw new Exception\InvalidArgumentException($msg);
                }
            }
        }
    }
}
