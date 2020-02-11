<?php

namespace D7ServiceContainer\DependencyInjection\DrupalCodeAnalyzer;

use ReflectionClass;
use ReflectionProperty;
use TokenReflection\ReflectionParameter;
use TokenReflection\Resolver;

class ArgumentExtractor {

    /**
     * @var ReflectionProperty
     */
    private $defaultProperty;

    public function __construct() {
        $reflector = new ReflectionClass(ReflectionParameter::class);
        $property  = $reflector->getProperty('defaultValueDefinition');
        $property->setAccessible(true);

        $this->defaultProperty = $property;
    }

    /**
     * @param ReflectionParameter $parameter
     *
     * @return ArgumentInfo
     */
    public function extractArgument(ReflectionParameter $parameter) {
        return new ArgumentInfo(
          $parameter->getName(),
          $parameter->getPosition(),
          $this->getType($parameter),
          $this->getDefaultValue($parameter)
        );
    }

    /**
     * @param ReflectionParameter $parameter
     *
     * @return string
     */
    private function getDefaultValue(ReflectionParameter $parameter) {
        $default           = ArgumentInfo::UNKNOWN_VALUE;
        $defaultDefinition = $this->defaultProperty->getValue($parameter);

        if (count($defaultDefinition) > 0) {
            $default = Resolver::getValueDefinition($defaultDefinition, $parameter);
        }

        return $default;
    }

    private function getType(ReflectionParameter $parameter) {
        if (!empty($parameter_class = $parameter->getClassName())) {
            return $parameter->getClassName();
        }
        else {
            return strtolower(trim($parameter->getOriginalTypeHint()));
        }
    }

}