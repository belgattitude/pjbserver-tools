<?php

if (!$loader = @include __DIR__ . '/../vendor/autoload.php') {
    die('You must set up the project dependencies, run the following commands:' . PHP_EOL .
            'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
            'php composer.phar install' . PHP_EOL);
}

$baseDir = dirname(__DIR__);
require_once dirname(__FILE__) . '/PjbServerTestConfig.php';

$loader = require __DIR__ . '/../vendor/autoload.php';
/*
$loader->add('Soluble', [$baseDir . '/src/', $baseDir . '/test/']);
$loader->register();
*/
