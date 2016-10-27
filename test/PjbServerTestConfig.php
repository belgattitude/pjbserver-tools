<?php

use PjbServer\Tools\StandaloneServer\Config;

class PjbServerTestConfig
{
    public static function getBaseDir()
    {
        return dirname(__DIR__);
    }

    /**
     * 
     * @return Config
     */
    public static function getStandaloneServerConfig()
    {
        $server_jar = $_SERVER['standalone_server_jar'];

        // test of relative path
        if (preg_match('/^\./', $server_jar)) {
            $server_jar = realpath(self::getBaseDir() . DIRECTORY_SEPARATOR . $server_jar);
        }

        $port = $_SERVER['standalone_server_port'];

        $params = [
            "port" => $port,
            "server_jar" => $server_jar,
            "log_file" => self::getBaseDir() . "/test/logs/pjbserver-port{$port}.log",
            "pid_file" => self::getBaseDir() . "/test/logs/pjbserver-port{$port}.pid",
            "classpaths" => [
                self::getBaseDir() . '/resources/autoload/*.jar'
            ]
        ];

        return new Config($params);
    }
}
