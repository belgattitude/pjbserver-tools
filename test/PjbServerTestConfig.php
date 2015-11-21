<?php

class PjbServerTestConfig
{
    
    /**
     * 
     * @return array
     */
    public static function getStandaloneServerConfig() {
        
        $config = array(
            "server_port" => $_SERVER['standalone_server_port'],
        );
        
        return $config;
    }
    
    
    
}
