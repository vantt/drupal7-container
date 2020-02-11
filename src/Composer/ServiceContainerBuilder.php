<?php
namespace D7ServiceContainer\Composer;

use \AppKernel;
use Composer\Script\Event;
use Composer\Installer\PackageEvent;


class ServiceContainerBuilder {

    public static function buildProductionServiceContainer(Event $event) {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        require $vendorDir . '/autoload.php';

        $kernel = new AppKernel('prod', FALSE);
        $kernel->loadClassCache();
        $kernel->boot();
    }
}