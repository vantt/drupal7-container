<?php namespace D7ServiceContainer;

use D7ServiceContainer\DependencyInjection\Compiler\RegisterDrupalCallbackPass;
use D7ServiceContainer\DependencyInjection\MioServiceContainerExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;


class MioServiceContainerBundle extends Bundle {

        public function getContainerExtension() {
            return new MioServiceContainerExtension();
        }

    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container) {
        parent::build($container);

        $container->addCompilerPass(new RegisterDrupalCallbackPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
        //$container->addCompilerPass(new CleanupPass(), PassConfig::TYPE_REMOVE);
        //$container->addCompilerPass(new RegisterDrupalSevicesPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);
        //$container->addCompilerPass(new RegisterMioSevicesPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 1);

    }

}

