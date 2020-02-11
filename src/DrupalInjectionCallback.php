<?php

namespace D7ServiceContainer;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DrupalInjectionCallback {

    // true is for dispatch
    // false is for non-dispatched
    const DISPATCH_HOOKS= [
      'user_login'  => true,
      'user_logout' => true,
      ];
    
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var null|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var null|LoggerInterface
     */
    private $logger;

    /**
     * DrupalInjectionCallback constructor.
     *
     * @param ContainerInterface            $container
     * @param EventDispatcherInterface|null $dispatcher
     * @param LoggerInterface|null          $logger
     */
    public function __construct(ContainerInterface $container, EventDispatcherInterface $dispatcher, LoggerInterface $logger = null) {
        $this->container  = $container;
        $this->logger     = $logger;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param       $callback_function
     * @param array $drupal_arguments
     *
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function execute($callback_function, $drupal_arguments = []) {
        $reset_arguments = [];

        // reset drupal-arguments to zero-based index
        foreach ($drupal_arguments as $key => $argument) {
            $reset_arguments[] = &$drupal_arguments[$key];
        }

        // if there is a service-declaration for the $callback_function
        // use parameter overwrite in that function
        // this is overwrite by index-based-position
        $representative_service = $this->findRepresentativeService($callback_function);

        if ($representative_service instanceof CallbackServiceRepresentation) {
            return $representative_service->execute($callback_function, $reset_arguments);
        }

        return call_user_func_array($callback_function, $reset_arguments);
    }

    /**
     * Dispatch drupal-hook as symfony event
     *
     * @param string $hook
     * @param        $arguments
     */
    public function dispatchEvent(string $hook, $arguments) {
        if ($this->dispatcher && !empty(self::DISPATCH_HOOKS[$hook])) {
            $name  = 'drupal.' . $hook;

            //dump($hook, $arguments);exit;

            $this->dispatcher->dispatch(new DrupalEvent($name, $arguments), $name);
        }
    }


    /**
     * Find a Representative Service for this callback
     *
     * @param string|array $callback
     *
     * @return CallbackServiceRepresentation|null
     */
    private function findRepresentativeService($callback): ?CallbackServiceRepresentation {

        $service_name = $this->generateServiceName($callback);

        // if there is service-declaration for the $callback_function
        // use parameter-overwrite in that function
        // this is overwrite by index-based
        if ($this->container->has($service_name)) {
            return $this->container->get($service_name);
        }

        return null;
    }

    private function generateServiceName($callback): string {
        $service_name = '';

        // return: drupal_functionName
        if (is_string($callback)) {
            $service_name = 'drupal_' . $callback;
        }
        // return: drupal_classFQN::methodName
        elseif (is_array($callback)) {
            $tmp    = $callback;
            $clazz  = array_shift($tmp);
            $method = array_shift($tmp);

            $class_name   = (is_object($clazz)) ? get_class($clazz) : $clazz;
            $service_name = 'drupal_' . ltrim(str_replace('\\\\', '\\', $class_name . '::' . $method), '\\');
        }

        return $service_name;
    }
}

