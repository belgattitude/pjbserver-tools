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

The java bridge standalone server can be used as an alternative to a J2EE bridge installation 
for php/java integration while keeping things simple for development or unit testing.
 
See the [soluble/japha](https://github.com/belgattitude/soluble-japha) project to get an info about php/java integration.  
  

## Features

- PJB standalone server wrapper in PHP.
- Includes latest compiled [JavaBridge.jar](./resources/pjb621_standalone/JavaBridge.jar) file.

## Requirements

- PHP 5.5+, 7.0 or HHVM >= 3.2.
- Linux/Unix environment.
- Java 1.7+ (see [install instructions](./doc/install_java.md)).

## Installation

Instant installation via [composer](http://getcomposer.org/).

```console
$ composer require "belgattitude/pjbserver-tools:^1.1.0"
```
Most modern frameworks will include Composer out of the box, but ensure the following file is included:

```php
<?php
// include the Composer autoloader
require 'vendor/autoload.php';
```


## Usage

### Start a standalone server

```php
<?php

use PjbServer\Tools\StandaloneServer;

$server = new StandaloneServer([
    'port' => '8089',
]);

try {
    $server->start();
} catch(\Exception $e) {
    // Exception message
    echo $e->getMessage();
    // Server output logs
    echo $server->getOutput();
    die();
}

$pid = $server->getPid();

$server->stop();

```

### Debugging

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
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR 0 Autoloading standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)


