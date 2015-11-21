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
     * @var array
     */
    protected $required_arguments = array(
        'server_port' => FILTER_VALIDATE_INT,
    );

    /**
     * Constructor
     *
     * @throws Exception\InvalidArgumentException
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->checkConfig($config);

        $this->config = $config;

    }

    /**
     * Check configuration parameters
     * @throws Exception\InvalidArgumentException
     * @param array $config
     */
    protected function checkConfig(array $config)
    {

        foreach ($this->required_arguments as $name => $type) {
            if (!isset($config[$name])) {
                $msg = "Missing option '$name' in Standalone server configuration";
                throw new Exception\InvalidArgumentException($msg);
            }

            if ($type !== null && !filter_var($config[$name], $type)) {
                $msg = "Unsupported type in option '$name' in Standalone server configuration (required $type)";
                throw new Exception\InvalidArgumentException($msg);
            }
        }
    }


    /**
     * Start the standalone server
     * @return void
     */
    public function start()
    {
        $port = $this->getServerPort();

        $command = "java -cp $jar_dir/mysql-connector-java-5.1.36-bin.jar:$jar_file php.java.bridge.Standalone SERVLET:$port";
        $error_file = "$test_dir/logs/pjb-error.log";
        $pid_file   = "$test_dir/logs/pjb-standalone.pid";

        if (!self::isStandaloneServerRunning($pid_file)) {
            echo "\nStarting standalone pjb server:\n $command\n";
            echo "@see logs in     : $error_file\n";
            echo "@see pid file in : $pid_file\n";

            $cmd = sprintf("%s > %s 2>&1 & echo $! > %s", $command, $error_file, $pid_file);
            exec($cmd);

            // let time for server to start
            if (preg_match('/travis/', dirname(__FILE__))) {
                sleep(8);
            } else {
                sleep(1);
            }
        } else {
            echo "Standalone server already running, skipping start\n";
        }

        register_shutdown_function(array(__CLASS__, 'killStandaloneServer'));

    }


    public function stop()
    {

    }

    public function restart()
    {

    }

    /**
     * Set port on which standalone server listens.
     *
     * @param int $port
     * @return void
     */
    public function setServerPort($port)
    {
        $this->port = $port;
    }

    /**
     * Return port on which standalone server listens
     * @return int
     */
    public function getServerPort()
    {
        return $this->port;
    }
}
