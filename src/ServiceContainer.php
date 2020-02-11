<?php
namespace D7ServiceContainer;

class ServiceContainer {

    /**
     * The currently active container object.
     *
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected static $container;

    /**
     * @param MioKernel $kernel
     * @param bool      $force_init
     */
    public function __construct(MioKernel $kernel, $force_init=FALSE) {
        $this->initContainer($kernel, $force_init);
    }

    /**
     * @param MioKernel $kernel
     *
     * @param bool      $force_init
     *
     * @internal param $environment
     * @internal param $debug
     */
    private function initContainer(MioKernel $kernel, $force_init=FALSE) {
        if (!self::$container || $force_init) {
            $kernel->loadClassCache();
            $kernel->boot();

            self::$container = $kernel->getContainer();
        }
    }

    /**
     * Returns the currently active global container.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    public static function getContainer() {
        return static::$container;
    }

    /**
     * Retrieves a service from the container.
     *
     * Use this method if the desired service is not one of those with a dedicated
     * accessor method below. If it is listed below, those methods are preferred
     * as they can return useful type hints.
     *
     * @param string $id
     *   The ID of the service to retrieve.
     * @return mixed
     *   The specified service.
     */
    public static function service($id) {
        if (!static::$container) {
            throw new \LogicException('Please run the initContainer() method before using the getContainer()');
        }

        return static::$container->get($id);
    }

}
