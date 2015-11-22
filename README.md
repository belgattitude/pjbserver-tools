# pjbserver-tools

[![Build Status](https://travis-ci.org/belgattitude/pjbserver-tools.svg?branch=master)](https://travis-ci.org/belgattitude/pjbserver-tools)
[![HHVM Status](http://hhvm.h4cc.de/badge/belgattitude/pjbserver-tools.png?style=flat)](http://hhvm.h4cc.de/package/belgattitude/pjbserver-tools)
[![Code Coverage](https://scrutinizer-ci.com/g/belgattitude/pjbserver-tools/badges/coverage.png?s=aaa552f6313a3a50145f0e87b252c84677c22aa9)](https://scrutinizer-ci.com/g/belgattitude/pjbserver-tools/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/belgattitude/pjbserver-tools/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/belgattitude/pjbserver-tools/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/belgattitude/pjbserver-tools/v/stable.svg)](https://packagist.org/packages/belgattitude/pjbserver-tools)
[![License](https://poser.pugx.org/belgattitude/pjbserver-tools/license.png)](https://packagist.org/packages/belgattitude/pjbserver-tools)


## Introduction

[PHP/Java bridge](http://php-java-bridge.sourceforge.net/pjb/) server tools 

## Features

- Provides standalone PJB server

## Requirements

- PHP 5.3+, 7.0 or HHVM >= 3.2.
- A supported JVM (java executable in path)
- Linux/Unix 

## Installation

`pjbserver-tools` can be installed through composer

```sh
php composer require belgattitude/pjbserver-tools:1.*
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
    echo $e->getMessage();
    die();
}

$pid = $server->getPid();
$output = $server->getOutput();
echo $output;

```

### Debugging

Some useful commands to watch, debug and eventually kill java standalone server process

```shell
> netstat -an | grep <port>
> ps ax | grep standalone
> kill <pid_standalone_server>
```

## Credits

Thanks to the fantastic PHPJavaBridge project leaders and contributors who made it possible. 
See their official homepage on http://php-java-bridge.sourceforge.net/pjb/index.php.

## Coding standards

* [PSR 4 Autoloader](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
* [PSR 2 Coding Style Guide](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)
* [PSR 1 Coding Standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md)
* [PSR 0 Autoloading standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)




