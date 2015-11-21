<?php

class PjbServerTestConfig
{
    
    public static function getBaseDir()
    {
        return dirname(__DIR__);
    }
    
    /**
     * 
     * @return array
     */
    public static function getStandaloneServerConfig() {
        
        $server_jar = $_SERVER['standalone_server_jar'];
        
        // test of relative path
        if (preg_match('/^\./', $server_jar)) {
            $server_jar = realpath(self::getBaseDir() . DIRECTORY_SEPARATOR . $server_jar);
        }
        
        $config = array(
            "port" => $_SERVER['standalone_server_port'],
            "server_jar" => $server_jar,
        );
        
        return $config;
    }
    
    
    
}
