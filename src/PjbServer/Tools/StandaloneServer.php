<?php
namespace PjbServer\Tools;

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
        'server_jar'  => 'existing_file',
        'error_file'  => 'file_with_existing_directory',
        'pid_file'    => 'file_with_existing_directory',
    );

    /**
     * Default configuration options
     * @var array
     */
    protected $default_config = array(
        'server_jar' => '{base_dir}/resources/pjb621_standalone/JavaBridge.jar',
        'java_bin'   => 'java',
        'error_file' => '{base_dir}/resources/pjb621_standalone/logs/pjbserver-port{tcp_port}-error.log',
        'pid_file'   => '{base_dir}/resources/pjb621_standalone/var/run/pjbserver-port{tcp_port}.pid'
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
     * @return void
     */
    public function start()
    {
        $port = $this->getServerPort();

        $java_bin = $this->config['java_bin'];

        $classpath = array(
            $this->config['server_jar'],
        );

        $classpath = join(':', $classpath);
        $command   = "$java_bin -cp $classpath php.java.bridge.Standalone SERVLET:$port";

        $error_file = $this->config['error_file'];
        $pid_file   = $this->config['pid_file'];
        $cmd = sprintf("%s > %s 2>&1 & echo $! > %s", $command, $error_file, $pid_file);

        //echo $cmd . "\n";
        exec($cmd);

        $this->started = true;


    }

    public function stop()
    {
        unlink($this->config['pid_file']);

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
        return trim(file_get_contents($pid_file));
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
     * Return default configuration options
     * @param int $tcp_port
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
