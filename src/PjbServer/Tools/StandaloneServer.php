<?php

namespace PjbServer\Tools;

use PjbServer\Tools\Network\PortTester;

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
    protected $required_arguments = [
        'port' => 'FILTER_VALIDATE_INT',
        'server_jar' => 'existing_file',
        'log_file' => 'file_with_existing_directory',
        'pid_file' => 'file_with_existing_directory',
        'autoload_path' => 'existing_directory'
    ];

    /**
     * Default configuration options
     * @var array
     */
    protected $default_config = [
        'server_jar' => '{base_dir}/resources/pjb621_standalone/JavaBridge.jar',
        'java_bin' => 'java',
        'log_file' => '{base_dir}/resources/pjb621_standalone/logs/pjbserver-port{tcp_port}.log',
        'pid_file' => '{base_dir}/resources/pjb621_standalone/var/run/pjbserver-port{tcp_port}.pid',
        'autoload_path' => '{base_dir}/resources/autoload'
    ];

    /**
     *
     * @var PortTester
     */
    protected $portTester;


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

        $curl_available = function_exists('curl_version');

        $this->portTester = new PortTester([
            'backend' => $curl_available ? PortTester::BACKEND_CURL : PortTester::BACKEND_STREAM_SOCKET,
            // Close timout ms could be adjusted for your system
            // It prevent that port availability testing does
            // not close quickly enough to allow standalone server binding
            'close_timeout_ms' => $curl_available ? null : 300
        ]);
    }

    /**
     * Start the standalone server
     *
     * @throws Exception\RuntimeException
     * @throws Exce
     *
     * @param int $timeout_ms maximum number of milliseconds to wait for server start
     * @return void
     */
    public function start($timeout_ms = 3000)
    {
        if ($this->isStarted()) {
            return;
        }

        $port = $this->getServerPort();

        if (!$this->portTester->isAvailable('localhost', $port, 'http')) {
            $msg = "Cannot start server on port '$port', it's already in use.";
            throw new Exception\RuntimeException($msg);
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
        $iterations = true;
        $refresh_us = 100 * 1000; // 100ms
        $timeout_us = $timeout_ms * 1000;
        $max_iterations = ceil($timeout_us / min([$refresh_us, $timeout_us]));

        while (!$started || $iterations > $max_iterations) {
            usleep($refresh_us);
            $log_file_content = file_get_contents($log_file);
            if (preg_match('/Exception/', $log_file_content)) {
                $msg = "Cannot start standalone server on port '$port', reason:\n";
                $msg .= $log_file_content;
                throw new Exception\RuntimeException($msg);
            }

            $log_file_content = file_get_contents($log_file);
            if (preg_match('/JavaBridgeRunner started on/', $log_file_content)) {
                $started = true;
            }
            $iterations++;
        }
        if (!$started) {
            $msg = "Standalone server probably not started, timeout '$timeout_ms' reached before getting output";
            throw new Exception\RuntimeException($msg);
        }
        $this->started = true;
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
            $running = $this->isProcessRunning($throws_exception=true);
            if (!$running) {
                if ($throws_exception) {
                    $msg = "Cannot stop: pid exists ($pid) but server process is not running";
                    throw new Exception\RuntimeException($msg);
                }
                return;
            }
        } catch (Exception\PidNotFoundException $e) {
            if ($throws_exception) {
                $msg = "Cannot stop server, pid file not found (was the server started ?)";
                throw new Exception\RuntimeException($msg);
            }
            return;
        }


        //$cmd = "kill $pid";
        // Let sleep the process,
        // @todo: test sleep mith microseconds on different unix flavours
        $sleep_time = '0.2';
        $cmd = sprintf("kill %d; while ps -p %d; do sleep %s;done;", $pid, $pid, $sleep_time);

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
     * Return command used to start the standalone server
     * @return string
     */
    public function getCommand()
    {
        $port = $this->getServerPort();

        $java_bin = $this->config['java_bin'];

        $jars = [];
        $autoload_path = $this->config['autoload_path'];
        $files = glob("$autoload_path/*.jar");
        foreach ($files as $file) {
            $jars[] = $file;
        }
        $jars[] = $this->config['server_jar'];

        $classpath = implode(':', $jars);

        $directives = ' -D' . implode(' -D', [
                    'php.java.bridge.daemon="false"',
                    'php.java.bridge.threads=30'
        ]);

        $command = "$java_bin -cp $classpath $directives php.java.bridge.Standalone SERVLET:$port";
        return $command;
    }


    /**
     * Get runnin standalone server pid number
     *
     * @throws Exception\PidNotFoundException
     * @return int
     */
    public function getPid()
    {
        $pid_file = $this->config['pid_file'];
        if (!file_exists($pid_file)) {
            $msg = "Pid file cannot be found '$pid_file'";
            throw new Exception\PidNotFoundException($msg);
        }
        $pid = trim(file_get_contents($pid_file));
        if (!preg_match('/^[0-9]+$/', $pid)) {
            $msg = "Pid found '$pid_file' but no valid pid stored in it or corrupted file '$pid_file'.";
            throw new Exception\PidCorruptedException($msg);
        }
        return (int) $pid;
    }


    /**
     * Return the content of the output_file
     * @throws \RuntimeException
     * @return string
     */
    public function getOutput()
    {
        $log_file = $this->config['log_file'];
        if (!file_exists($log_file)) {
            throw new Exception\RuntimeException("Server output log file does not exists '$log_file'");
        } elseif (!is_readable($log_file)) {
            throw new Exception\RuntimeException("Cannot read log file do to missing read permission '$log_file'");
        }
        $output = file_get_contents($log_file);
        return $output;
    }


    /**
     * Test whether the standalone server process
     * is effectively running
     *
     * @throws Exception\PidNotFoundException
     * @param $throwsException if false discard exception if pidfile not exists
     * @return boolean
     */
    public function isProcessRunning($throwsException = false)
    {
        $running = false;
        try {
            $pid = $this->getPid();
            $result = trim(shell_exec(sprintf("ps -j --no-headers -p %d", $pid)));
            if (preg_match("/^$pid/", $result)) {
                $running = true;
            }
        } catch (Exception\PidNotFoundException $e) {
            if ($throwsException) {
                throw $e;
            }
        }
        return $running;
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
                    case 'existing_directory':
                        $directory = $config[$name];
                        if (!is_dir($directory)) {
                            $msg = "The '$name' directory '$directory 'does not exists.";
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
