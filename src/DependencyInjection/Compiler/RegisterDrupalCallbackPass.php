<?php

namespace D7ServiceContainer\DependencyInjection\Compiler;

use D7ServiceContainer\CallbackServiceRepresentation;
use D7ServiceContainer\DependencyInjection\DrupalCodeAnalyzer\CallbackInfo;
use D7ServiceContainer\DependencyInjection\DrupalCodeAnalyzer\TokenReflectionCallbackAnalyzer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;


class RegisterDrupalCallbackPass implements CompilerPassInterface {

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function process(ContainerBuilder $container) {

        if ($container->hasParameter('kernel.drupal_dir')) {
            $drupal_analyzer = new TokenReflectionCallbackAnalyzer($this->getDrupalDir($container));

            /** @var CallbackInfo $callbackInfo */
            foreach ($drupal_analyzer->getCallbacks() as $callbackInfo) {
                $serviceDefinition = $this->findServiceDefinition($callbackInfo->getServiceName(), $container);
                ServiceDefinitionBuilder::fixDefinition($callbackInfo, $serviceDefinition);
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return string
     */
    private function getDrupalDir(ContainerBuilder $container): string {
        return $container->getParameterBag()->resolveValue($container->getParameter('kernel.drupal_dir'));
    }

    /**
     * @param string           $service_name
     * @param ContainerBuilder $containerBuilder
     *
     * @return Definition
     */
    private function findServiceDefinition(string $service_name, ContainerBuilder $containerBuilder): Definition {

        try {
            // first find a service-definition for this function-name
            $serviceDefinition = $containerBuilder->findDefinition($service_name);

        } // if there is no service definition for this function_name, register a new one.
        catch (ServiceNotFoundException $exception) {

            $serviceDefinition = new ChildDefinition(CallbackServiceRepresentation::PARENT_SERVICE_NAME);
            $containerBuilder->setDefinition($service_name, $serviceDefinition);
        }

        return $serviceDefinition;
    }
}