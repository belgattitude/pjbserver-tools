<?php

namespace PjbServer\Tools;

/*
java -Djava.awt.headless="true"
     -Dphp.java.bridge.threads=50
     -Dphp.java.bridge.base=/usr/lib/php/modules
     -Dphp.java.bridge.php_exec=/usr/local/bin/php-cgi
     -Dphp.java.bridge.default_log_file=
     -Dphp.java.bridge.default_log_level=5
     -Dphp.java.bridge.daemon="false"
     -jar JavaBridge.jar
*/


class StandaloneServer
{

    /**
     * @var int
     */
    protected $port;

    /**
     *
     * @var array
     */
    protected $config;

    /**
     * Tells whether the standalone server is started
     * @var boolean
     */
    protected $started = false;

    /**
     * @var array
     */
    protected $required_arguments = array(
        'port' => 'FILTER_VALIDATE_INT',
        'server_jar' => 'existing_file',
        'log_file' => 'file_with_existing_directory',
        'pid_file' => 'file_with_existing_directory',
    );

    /**
     * Default configuration options
     * @var array
     */
    protected $default_config = array(
        'server_jar' => '{base_dir}/resources/pjb621_standalone/JavaBridge.jar',
        'java_bin' => 'java',
        'log_file' => '{base_dir}/resources/pjb621_standalone/logs/pjbserver-port{tcp_port}.log',
        'pid_file' => '{base_dir}/resources/pjb621_standalone/var/run/pjbserver-port{tcp_port}.pid'
    );

    /**
     * Constructor
     *
     * <code>
     *
     * $config = array(
     *      'port' => 8089,
     *
     *      // optionally
     *      'server_jar' => 'path/to/JavaBridge.jar'
     *      'java_bin' => 'path/to/java'
     * );
     * $server = new StandaloneServer($config);
     *
     * </code>
     *
     * @throws Exception\InvalidArgumentException
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (!isset($config['port'])) {
            throw new Exception\InvalidArgumentException("Error missing required 'port' in config");
        } elseif (!filter_var($config['port'], FILTER_VALIDATE_INT)) {
            throw new Exception\InvalidArgumentException("Option 'port' must be numeric");
        }
        $config = array_merge($this->getDefaultConfig($config['port']), $config);
        $this->checkConfigRequiredArgs($config);
        $this->setServerPort($config['port']);
        $this->config = $config;
    }

    /**
     * Start the standalone server
     *
     * @throws Exception\RuntimeException
     *
     * @return void
     */
    public function start()
    {

        if ($this->isStarted()) {
            return;
        }

        $port = $this->getServerPort();

        if (!$this->isPortAvailable('localhost', $port)) {
            $msg = "Cannot start server on port '$port', it's already in use.";
            //throw new Exception\RuntimeException($msg);
        }

        $command = $this->getCommand();

        $log_file = $this->config['log_file'];
        $pid_file = $this->config['pid_file'];
        $cmd = sprintf("%s > %s 2>&1 & echo $! > %s", $command, $log_file, $pid_file);

        exec($cmd);

        if (!file_exists($pid_file)) {
            $msg = "Server not started, pid file was not created in '$pid_file'";
            throw new Exception\RuntimeException($msg);
        }
        if (!file_exists($log_file)) {
            $msg = "Server not started, log file was not created in '$log_file'";
            throw new Exception\RuntimeException($msg);
        }

        // Loop for waiting correct start of phpjavabridge
        $started = false;
        $refresh_ms = 200; // 200ms
        while (!$started) {
            usleep(200 * 1000);
            $log_file_content = file_get_contents($log_file);
            if (preg_match('/Exception/', $log_file_content)) {
                $msg = "Cannot start standalone server on port '$port', reason:\n";
                $msg .= $log_file_content;
                throw new Exception\RuntimeException($msg);
            }

            clearstatcache($clear_realpath_cache = false, $log_file);
            $log_file_content = file_get_contents($log_file);
            if (preg_match('/JavaBridgeRunner started on/', $log_file_content)) {
                $started = true;
            }

        }
        $this->started = true;
    }

    /**
     * Check if TCP port is available for binding
     *
     * @param int $port
     * @param int $timeout
     */
    protected function isPortAvailable($host, $port, $timeout = 1)
    {
        $available = false;
        $fp = @stream_socket_client("tcp://$host:$port", $errno, $errstr, $timeout);
        if (!$fp) {
            $available = true;
        } else {
            fclose($fp);
        }
        return $available;
    }

    /**
     * Return command used to start the standalone server
     * @return string
     */
    public function getCommand()
    {
        $port = $this->getServerPort();

        $java_bin = $this->config['java_bin'];

        $classpath = join(':', array(
            $this->config['server_jar'],
        ));

        $directives = ' -D' . join(' -D', array(
            'php.java.bridge.daemon="false"',
            'php.java.bridge.threads=50'
        ));

        $command = "$java_bin -cp $classpath $directives php.java.bridge.Standalone SERVLET:$port";
        return $command;
    }

    /**
     * Stop the standalone server
     *
     * @throws Exception\RuntimeException
     * @param boolean $throws_exception wether to throw exception if pid or process cannot be found or killed.
     * @return void
     */
    public function stop($throws_exception = false)
    {
        $pid_file = $this->config['pid_file'];

        try {
            $pid = $this->getPid();
        } catch (Exception\RuntimeException $e) {
            $msg = "Cannot stop server, pid cannot be determined (was the server started ?)";
            if ($throws_exception) {
                throw $e;
            }
        }

        $cmd = "kill $pid";

        exec($cmd, $output, $return_var);

        try {
            if ($return_var !== 0) {
                $msg = "Cannot kill standalone server process '$pid', seems to not exists.";
                throw new Exception\RuntimeException($msg);
            }
        } catch (Exception\RuntimeException $e) {
            if ($throws_exception) {
                if (file_exists($pid_file)) {
                    unlink($pid_file);
                }
                throw $e;
            }
        }

        if (file_exists($pid_file)) {
            unlink($pid_file);
        }

        $this->started = false;
    }

    /**
     * Tells whether the standalone server is started
     * @return boolean
     */
    public function isStarted()
    {
        return $this->started;
    }

    /**
     * Get runnin standalone server pid number
     *
     * @throws Exception\RuntimeException
     * @return int
     */
    public function getPid()
    {
        $pid_file = $this->config['pid_file'];
        if (!file_exists($pid_file)) {
            $msg = "Pid file cannot be found '$pid_file'";
            throw new Exception\RuntimeException($msg);
        }
        $pid = trim(file_get_contents($pid_file));
        if (!is_numeric($pid)) {
            $msg = "Pid found '$pid_file' but no valid pid stored in it.";
            throw new Exception\RuntimeException($msg);
        }
        return $pid;
    }

    /**
     * Restart the standalone server
     */
    public function restart()
    {
        $this->stop();
        $this->start();
    }

    /**
     * Return port on which standalone server listens
     * @return int
     */
    public function getServerPort()
    {
        return $this->port;
    }

    /**
     * Set port on which standalone server listens.
     *
     * @param int $port
     * @return void
     */
    protected function setServerPort($port)
    {
        $this->port = $port;
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
        $base_dir = realpath(__DIR__ . '/../../../');
        $config = array();
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
    protected function checkConfigRequiredArgs(array $config)
    {
        foreach ($this->required_arguments as $name => $type) {
            if (!isset($config[$name])) {
                $msg = "Missing option '$name' in Standalone server configuration";
                throw new Exception\InvalidArgumentException($msg);
            }
            if (is_long($type) && !filter_var($config[$name], $type)) {
                $msg = "Unsupported type in option '$name' in Standalone server configuration (required $type)";
                throw new Exception\InvalidArgumentException($msg);
            } elseif (is_string($type)) {
                switch ($type) {
                    case 'existing_file':
                        $file = $config[$name];
                        if (!file_exists($file)) {
                            $msg = "The '$name' file '$file 'does not exists.";
                            throw new Exception\InvalidArgumentException($msg);
                        }
                        break;
                    case 'file_with_existing_directory':
                        $file = $config[$name];
                        $info = pathinfo($file);
                        $dirname = $info['dirname'];
                        if (!is_dir($dirname) || $dirname == ".") {
                            $msg = "Option '$name' refer to an invalid or non-existent directory ($file)";
                            throw new Exception\InvalidArgumentException($msg);
                        }
                        if (is_dir($file)) {
                            $msg = "Option '$name' does not refer to a file but an existing directory ($file)";
                            throw new Exception\InvalidArgumentException($msg);
                        }
                        if (file_exists($file) && !is_writable($file)) {
                            $msg = "File specified in '$name' is not writable ($file)";
                            throw new Exception\InvalidArgumentException($msg);
                        }
                        break;
                }
            }
        }
    }
}
