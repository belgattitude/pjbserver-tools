# pjbserver-tools
[![PHP Version](http://img.shields.io/badge/php-5.5+-ff69b4.svg)](https://packagist.org/packages/belgattitude/pjbserver-tools)
[![Build Status](https://travis-ci.org/belgattitude/pjbserver-tools.svg?branch=master)](https://travis-ci.org/belgattitude/pjbserver-tools)
[![HHVM Status](http://hhvm.h4cc.de/badge/belgattitude/pjbserver-tools.svg)](http://hhvm.h4cc.de/package/belgattitude/pjbserver-tools)
[![Code Coverage](https://scrutinizer-ci.com/g/belgattitude/pjbserver-tools/badges/coverage.png?s=aaa552f6313a3a50145f0e87b252c84677c22aa9)](https://scrutinizer-ci.com/g/belgattitude/pjbserver-tools/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/belgattitude/pjbserver-tools/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/belgattitude/pjbserver-tools/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/belgattitude/pjbserver-tools/v/stable.svg)](https://packagist.org/packages/belgattitude/pjbserver-tools)
[![Total Downloads](https://poser.pugx.org/belgattitude/pjbserver-tools/downloads.svg)](https://packagist.org/packages/belgattitude/pjbserver-tools)
[![License](https://poser.pugx.org/belgattitude/pjbserver-tools/license.svg)](https://packagist.org/packages/belgattitude/pjbserver-tools)


## Introduction

The `pjbserver-tools` package currently provides an easy installable php java bridge standalone server.
See the [soluble/japha](https://github.com/belgattitude/soluble-japha) project to get more info about php/java integration.

*The java bridge standalone server can be used as an alternative to a J2EE bridge installation 
for php/java integration while keeping things simple for development, unit testing or small projects.*     

## Features

- Easy setup of a PHP Java bridge standalone server (*nix).
- Console commands to control the server (start/stop/restart/status). 
- API library to customize the behaviour.
- Includes latest compiled [JavaBridge.jar](./resources/pjb621_standalone/JavaBridge.jar) file.

## Requirements

- PHP 5.5+, 7.0 or HHVM >= 3.2.
- Linux/Unix *(Mac OSX 10.1+ reported working)*.
- Java 1.7+ (see [install instructions](./doc/install_java.md)).

## Installation

Depending on your needs you can use the pjserver-tools in two ways.


1. Option 1: clone the repo or download the repo (typical: command line)

   First create a path on your filesystem that will hold the server install.        
   
   ```console
   $ mkdir -p /my/path/pjbserver-tools
   $ cd /my/path/pjbserver-tools
   ```
   
   Clone the repository and use run [composer](http://getcomposer.org) update.
   
   ```console
   $ git clone https://github.com/belgattitude/pjbserver-tools.git .
   $ composer update
   ```
   
2. Option 2: Add library to your project (API)
   
   If you want more control over the process and don't want to install the command line setup,
   you can add the project to your dependencies with [composer](http://getcomposer.org/).

    ```console
    $ composer require belgattitude/pjbserver-tools
    ```
    

## Usage

### With the command line standalone server

If you've choosen the typical console mode (first installation method), You can use the commands 
`pjbserver:start`, `pjbserver:stop`, `pjbserver:restart`, `pjbserver:status` followed
by the `pjbserver.config.php` file to control or query the server status. 


```console
$ ./bin/pjbserver-tools pjbserver:start -vvv ./config/pjbserver.config.php.dist
$ ./bin/pjbserver-tools pjbserver:stop -vvv ./config/pjbserver.config.php.dist
$ ./bin/pjbserver-tools pjbserver:restart -vvv ./config/pjbserver.config.php.dist
$ ./bin/pjbserver-tools pjbserver:status -vvv ./config/pjbserver.config.php.dist

$ # for listing the java cli command issued : 
$ ./bin/pjbserver-tools pjbserver:reveal ./config/pjbserver.config.php.dist
```
 

If you use the [./config/pjbserver.config.php.dist](./config/pjbserver.config.php.dist) config file, the server will start on port ***8089***. 
   
Feel free to create a local copy of this file and adapt for your usage :
 
```console
$ cp ./config/pjbserver.config.php.dist /my/path/pjbserver.config.php
```

*Note that the -v, -vv, -vvv option in the command line allows to define the verbosity level of the scripts.*

### Using the API (programatically)

As an alternative to the command line you can control the server directly from PHP.

Here's a little example:

```php
<?php

use PjbServer\Tools\StandaloneServer;
use PjbServer\Tools\StandaloneServer\Config;

$tcp_port = 8089;

$config = new Config([
    // Port on which php java bridge server listen (required)
    'port' => $tcp_port,
    
    /**
     * Location of log and pid files...
     * Defaults is to put them in the project 'pjbserver-tools/var/...' directory
     * which is fine for unit testing, but to prevent loosing those files
     * set a safe directory (not /tmp as it might be cleared by the OS)
     */
    //'log_file'   => "/my/path/var/pjbserver-port${tcp_port}.log",
    //'pid_file'   => "/my/path/var/pjbserver-port${tcp_port}.pid",
    

    // Optional but often more than useful
    'classpaths'  => [
          '/my/path/*.jar',
          '/another/path/mylib.jar'
    ],
   
    // Standalone server tuning
    // Number of threads for standalone server is 50, increase if needed
    //'threads'    => 50,
       
    // Java binary
    // change location if you like, for example
    // /usr/lib/jvm/java-8-oracle/bin/java
    'java_bin' => 'java', 

    /**
     * Location of the JavaBridge.jar,
     * Default is to use the default (included) one
     * available in pjbserver-tools/resources/pjb61_standalone/JavaBridge.jar
     */
    //'server_jar' => "/my/path/pjb621_standalone/JavaBridge.jar",    
]);

$server = new StandaloneServer($config);

try {
    $server->start();
} catch(\Exception $e) {
    // Exception message
    echo $e->getMessage();
    // Server output logs
    echo $server->getOutput();
    die();
}

echo "Started: " . ($server->isStarted() ? 'yes' : 'no') . PHP_EOL;
echo "Running: " . ($server->isProcessRunning() ? 'yes' : 'no') . PHP_EOL;
echo "Pid    : " . $server->getPid() . PHP_EOL;

// Stopping the server

$server->stop();

```

You can also inject any PSR-3 compatible logger to the `StandaloneServer`.

```php
// any PSR-3 compatible logger
$logger = new \Psr\Log\NullLogger();
$server = new StandaloneServer($config, $logger);

```

## Configuration

The dist config file [./config/pjbserver.config.php.dist](https://github.com/belgattitude/pjbserver-tools/blob/master/config/pjbserver.config.php.dist)
contains the default parameters used in console mode.  

### Parameters

| Key            | Type   | Comment                                          |
|----------------|--------|--------------------------------------------------|
| `port`         | int    | TCP port on which standalone server listen       |
| `classpaths`   | array  | Java additionnal classpaths                      |
| `threads`      | int    | Server max number of threads                     |
| `java_bin`     | string | Java binary executable (with or without path)    |
| `server_jar`   | string | Path to the JavaBridge.jar file                  |
| `log_file`     | string | Path to the standalone server log file           |
| `pid_file`     | string | Path to the standalone pid file                  |

Some considerations:

- When choosing a `port`, ensure it's not available publicly (security).
- The default config set `log_file` and `pid_file` in the ./var directory, please
  change the default location to your data directory.
- Avoid storing `log_file` and `pid_file` in the global temp directory '/tmp' as it might
  be cleared by the OS at anytime.  

### Classpath configuration

Whenever you need to add some java libraries, simply edit the configuration file and look for the
`classpaths` option and add the required jar files.

As an example:

```php
<?php

return [
    'port'       => 8089,
    'classpaths' => [
        '/my/path/autoload/mysql-connector.jar',
        '/my/autoload_path/*.jar
    ],

];
```

Don't forget to restart the standalone server to reflect the changes.

*Using wildcard `/my/path/*.jar` is possible but should be used with care. All matching files will be appended to classpath 
by passing arguments in a shell exec. Limits exists...*

## Debugging

Some useful commands to watch, debug and eventually kill java standalone server process

Console style:

```console
$ ./bin/pjbserver-tools pjbserver:status -vvv ./config/pjbserver.config.php.dist
```

Unix style:

```console
> netstat -an | grep <port>
> ps ax | grep standalone
> kill <pid_standalone_server>
```

## Tools

### Create a war bundle

Some scripts and ant tasks examples are available in the /tools folder.

## Coding standards

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 3 Logger interface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
