<?php

// Bootstrap
try {
    // Step 1: init autoloader

    $autoloadFiles = [__DIR__ . '/../vendor/autoload.php',
                           __DIR__ . '/../../../autoload.php'];

    $found = false;
    foreach ($autoloadFiles as $autoloadFile) {
        if (file_exists($autoloadFile)) {
            $found = true;
            require_once $autoloadFile;
            break;
        }
    }
    if (!$found) {
        throw new \Exception('Cannot find composer vendor autoload, run composer update');
    }
} catch (\Exception $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}

$cli = new Symfony\Component\Console\Application('pjb-server console', '2.0.0');
$cli->setCatchExceptions(true);

$commandRepo = new \PjbServer\Tools\Console\CommandRepository();

$cli->addCommands($commandRepo->getRegisteredCommands());

// helpers
$helpers = [
    'question' => new Symfony\Component\Console\Helper\QuestionHelper(),
];
foreach ($helpers as $name => $helper) {
    $cli->getHelperSet()->set($helper, $name);
}

$cli->run();
