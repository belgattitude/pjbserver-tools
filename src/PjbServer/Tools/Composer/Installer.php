<?php

declare(strict_types=1);

namespace PjbServer\Tools\Composer;

use Composer\Script\Event;

//use Composer\Installer\PackageEvent;

class Installer
{
    public static function postInstall(Event $event): void
    {
        //var_dump($event);
    }

    public static function postUpdate(Event $event): void
    {
        //var_dump($event);
        /*
        $package = $event->getName();
        $installManager = $event->getComposer()->getInstallationManager();

        echo $installManager->getInstallPath($package);
        */
    }
}
