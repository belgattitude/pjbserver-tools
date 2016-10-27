<?php

namespace PjbServer\Tools;

use PjbServer\Tools\Network\PortTester;
use PjbServer\Tools\System\Process;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;


class StandaloneServer
{

    /**
     * @var int
     */
    protected $port;

    /**
     *
     * @var StandaloneServer\Config
     */
    protected $config;

    /**
     * Tells whether the standalone server is started
     * @var boolean
     */
    protected $started = false;


    /**
     *
     * @var PortTester
     */
    protected $portTester;


    /**
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * @var Process
     */
    protected $process;

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
     * @param StandaloneServer\Config $config
     * @param LoggerInterface $logger
     *
     */
    public function __construct(StandaloneServer\Config $config, LoggerInterface $logger=null)
    {
        $this->config = $config;

        $curl_available = function_exists('curl_version');

        $this->portTester = new PortTester([
            'backend' => $curl_available ? PortTester::BACKEND_CURL : PortTester::BACKEND_STREAM_SOCKET,
            // Close timout ms could be adjusted for your system
            // It prevent that port availability testing does
            // not close quickly enough to allow standalone server binding
            'close_timeout_ms' => $curl_available ? null : 300
        ]);
        if ($logger === null) {
            $logger = new NullLogger();
        }
        $this->logger = $logger;

        $this->process = new Process();
    }

    /**
     * Start the standalone server
     *
     * @throws Exception\RuntimeException
     *
     * @param int $timeout_ms maximum number of milliseconds to wait for server start
     * @return void
     */
    public function start($timeout_ms = 3000)
    {
        $port = $this->config->getPort();

        $this->logger->notice("Starting standalone server on port $port.");

        if ($this->isStarted()) {
            $this->logger->notice("Standalone server already running, skipping start.");
            return;
        }

        if (!$this->portTester->isAvailable('localhost', $port, 'http')) {
            $msg = "Cannot start server on port '$port', it's already in use.";
            $this->logger->error("Start failed: $msg");
            throw new Exception\PortUnavailableException($msg);
        }

        $command = $this->getCommand();

        $log_file = $this->config->getLogFile();
        $pid_file = $this->config->getPidFile();
        $cmd = sprintf("%s > %s 2>&1 & echo $! > %s", $command, $log_file, $pid_file);

        $this->logger->debug("Start server with: $cmd");
        exec($cmd);

        if (!file_exists($pid_file)) {
            $msg = "Server not started, pid file was not created in '$pid_file'";
            $this->logger->error("Start failed: $msg");
            throw new Exception\RuntimeException($msg);
        }
        if (!file_exists($log_file)) {
            $msg = "Server not started, log file was not created in '$log_file'";
            $this->logger->error("Start failed: $msg");
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
                $this->logger->error("Start failed: $msg");
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
            $this->logger->error("Start failed: $msg");
            throw new Exception\RuntimeException($msg);
        }
        $this->started = true;
    }



    /**
     * Stop the standalone server
     *
     * @throws Exception\StopFailedException
     * @param boolean $throwException whether to throw exception if pid exists in config but process cannot be found
     * @param boolean $clearPidFileOnException clear th pid file if the server was not running
     * @return void
     */
    public function stop($throwException=false, $clearPidFileOnException=false)
    {
        $this->logger->notice("Stopping server");

        try {
            $pid = $this->getPid();
            $running = $this->isProcessRunning(true);
            if (!$running) {
                if ($throwException) {
                    $msg = "Cannot stop: pid exists (${pid}) but server process is not running (throws_exception=true)";
                    $this->logger->notice("Stop failed: ${msg}");
                    throw new Exception\StopFailedException($msg);
                }
                return;
            }
        } catch (Exception\PidNotFoundException $e) {
            if ($throwException) {
                $msg = "Cannot stop server: pid file not found (was the server started ?)";
                $this->logger->notice("Stop failed: $msg");
                if ($clearPidFileOnException) {
                    $this->clearPidFile();
                }
                throw new Exception\StopFailedException($msg, null, $e);
            }
            return;
        }

        $killed = $this->process->kill($pid, $wait=true);

        try {
            if (!$killed) {
                $msg = "Cannot kill standalone server process '$pid', seems to not exists.";
                $this->logger->notice("Stop failed: $msg");
                throw new Exception\RuntimeException($msg);
            }
        } catch (Exception\RuntimeException $e) {
            if ($throwException) {
                $this->clearPidFile();
                throw $e;
            }
        }

        // Server successfully stopped let's clear the pid
        $this->clearPidFile();
        $this->started = false;
    }


    /**
     * @throws Exception\FilePermissionException
     */
    protected function clearPidFile()
    {
        $pid_file = $this->config->getPidFile();
        if (file_exists($pid_file)) {
            if (is_writable($pid_file)) {
                unlink($pid_file);
            } else {
                throw new Exception\FilePermissionException("Cannot remove pid file '${pid_file}', no write access");
            }
        }
    }

    /**
     * Tells whether the standalone server is started
     *
     * @param boolean $test_is_running
     * @return boolean
     */
    public function isStarted($test_is_running=true)
    {
        // In case of previous run, let's us
        if (!$this->started && $test_is_running) {
            $this->started = $this->isProcessRunning();
        }
        return $this->started;
    }

    /**
     * Return command used to start the standalone server
     * @return string
     */
    public function getCommand()
    {
        $port = $this->config->getPort();

        $java_bin = $this->config->getJavaBin();

        $jars = [];
        $classpaths = $this->config->getClasspaths();
        foreach ($classpaths as $classpath) {
            if (preg_match('/\*\.jar$/', $classpath)) {
                $directory = preg_replace('/\*\.jar$/', '', $classpath);
                $files = glob("$directory/*.jar");
                foreach ($files as $file) {
                    foreach ($files as $file) {
                        $jars[] = $file;
                    }
                }
            } else {
                $jars[] = $classpath;
            }
        }

        $jars[] = $this->config->getServerJar();
        $classpath = implode(':', $jars);
        $threads = $this->config->getThreads();

        $directives = ' -D' . implode(' -D', [
                    'php.java.bridge.daemon="false"',
                    "php.java.bridge.threads=$threads"
        ]);

        $command = sprintf('%s -cp "%s" %s php.java.bridge.Standalone SERVLET:%d',
                            $java_bin, $classpath, $directives, $port);

        return $command;
    }


    /**
     * Get standalone server pid number as it was stored during last start
     *
     * @throws Exception\PidNotFoundException|ExceptionPidCorruptedException
     * @return int
     */
    public function getPid()
    {
        $pid_file = $this->config->getPidFile();
        if (!file_exists($pid_file)) {
            $msg = "Pid file cannot be found '$pid_file'";
            $this->logger->info("Get PID failed: $msg");
            throw new Exception\PidNotFoundException($msg);
        }
        $pid = trim(file_get_contents($pid_file));
        if (!preg_match('/^[0-9]+$/', $pid)) {
            $msg = "Pid found '$pid_file' but no valid pid stored in it or corrupted file '$pid_file'.";
            $this->logger->error("Get PID failed: $msg");
            throw new Exception\PidCorruptedException($msg);
        }
        return (int) $pid;
    }


    /**
     * Return the content of the output_file
     * @throws Exception\RuntimeException
     * @return string
     */
    public function getOutput()
    {
        $log_file = $this->config->getLogFile();
        if (!file_exists($log_file)) {
            $msg = "Server output log file does not exists '$log_file'";
            $this->logger->error("Get server output failed: $msg");
            throw new Exception\RuntimeException($msg);
        } elseif (!is_readable($log_file)) {
            $msg = "Cannot read log file do to missing read permission '$log_file'";
            $this->logger->error("Get server output failed: $msg");
            throw new Exception\RuntimeException($msg);
        }
        $output = file_get_contents($log_file);
        return $output;
    }


    /**
     * Test whether the standalone server process
     * is effectively running
     *
     * @throws Exception\PidNotFoundException
     * @param boolean $throwsException if false discard exception if pidfile not exists
     * @return boolean
     */
    public function isProcessRunning($throwsException=false)
    {
        $running = false;
        try {
            $pid = $this->getPid();
            $isRunning = $this->process->isRunning($pid);
            if ($isRunning) {
                $this->logger->debug("Pid '${pid}' running.");
                $running = true;
            } else {
                $this->logger->debug("Pid '${pid}' not running.");
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
     * Return underlying configuration object
     * @return StandaloneServer\Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}
