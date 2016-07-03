# pjbserver-tools

[![Build Status](https://travis-ci.org/belgattitude/pjbserver-tools.svg?branch=master)](https://travis-ci.org/belgattitude/pjbserver-tools)
[![HHVM Status](http://hhvm.h4cc.de/badge/belgattitude/pjbserver-tools.png?style=flat)](http://hhvm.h4cc.de/package/belgattitude/pjbserver-tools)
[![Code Coverage](https://scrutinizer-ci.com/g/belgattitude/pjbserver-tools/badges/coverage.png?s=aaa552f6313a3a50145f0e87b252c84677c22aa9)](https://scrutinizer-ci.com/g/belgattitude/pjbserver-tools/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/belgattitude/pjbserver-tools/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/belgattitude/pjbserver-tools/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/belgattitude/pjbserver-tools/v/stable.svg)](https://packagist.org/packages/belgattitude/pjbserver-tools)
[![Total Downloads](https://poser.pugx.org/belgattitude/pjbserver-tools/downloads.png)](https://packagist.org/packages/belgattitude/pjbserver-tools)
[![License](https://poser.pugx.org/belgattitude/pjbserver-tools/license.png)](https://packagist.org/packages/belgattitude/pjbserver-tools)


## Introduction

The `pjbserver-tools` package currently provides an easy installable php java bridge standalone server.
See the [soluble/japha](https://github.com/belgattitude/soluble-japha) project to get more info about php/java integration.


*The java bridge standalone server can be used as an alternative to a J2EE bridge installation 
for php/java integration while keeping things simple for development, unit testing or small projects.*
     

## Features

- PHP Java bridge standalone server.
- Simple and easy console to start/stop the server. 
- Libraries to programatically control the server.
- Includes latest compiled [JavaBridge.jar](./resources/pjb621_standalone/JavaBridge.jar) file.

## Requirements

- PHP 5.5+, 7.0 or HHVM >= 3.2.
- Linux/Unix environment.
- Java 1.7+ (see [install instructions](./doc/install_java.md)).

## Installation
 
Instant installation via [composer](http://getcomposer.org/).

```console
$ composer require "belgattitude/pjbserver-tools:^2.0.0"
```


## Usage

### Standalone server

Depending on your needs, you can decide to use the standalone server directly from the command line or use it programatically.
 
#### Option 1: Command line usage

First copy the distribution configuration file and edit a local copy.

```console
$ cp ./vendor/belgattitude/pjbserver-tools/config/pjbserver.config.php.dist ./pjbserver.config.php
```

Edit the TCP server port on which you want the standalone server to listen and eventually advanced options.

Then you can control the server from the command line.

```console
$ ./vendor/bin/pjbserver-tools pjbserver:start ./pjbserver.config.php
$ ./vendor/bin/pjbserver-tools pjbserver:stop ./pjbserver.config.php
$ ./vendor/bin/pjbserver-tools pjbserver:restart ./pjbserver.config.php
```

*It's possible to launch the process at boot time ([supervisord](http://supervisord.org/),...), but for production systems
the best is to deploy on a J2EE server like Tomcat...*

#### Option 2: Programatically

As an alternative to the command line you can control the server directly from PHP.

```php
<?php

use PjbServer\Tools\StandaloneServer;
use PjbServer\Tools\StandaloneServer\Config;

$config = new Config([
    // Port on which php java bridge server listen (required)
    'port' => 8089,

    // Optional but often more than useful
    'classpaths'  => [
          '/my/path/*.jar',
          '/another/path/mylib.jar'
    ],
    
   
    // Optional
    'java_bin' => 'java', // Java executable
    'server_jar' => '{base_dir}/resources/pjb621_standalone/JavaBridge.jar',
    'log_file'   => '{base_dir}/var/pjbserver-port{tcp_port}.log',
    'pid_file'   => '{base_dir}/var/pjbserver-port{tcp_port}.pid',
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
$server = new StanaloneServer($config, $logger);

```

## Debugging


Some useful commands to watch, debug and eventually kill java standalone server process

```shell
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
* [PSR 0 Autoloading standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)


