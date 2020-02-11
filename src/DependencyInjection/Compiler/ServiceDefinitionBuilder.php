<?php

namespace D7ServiceContainer\DependencyInjection\Compiler;


use D7ServiceContainer\CallbackServiceRepresentation;
use D7ServiceContainer\DependencyInjection\DrupalCodeAnalyzer\CallbackInfo;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;
use Symfony\Component\DependencyInjection\Reference;

class ServiceDefinitionBuilder {

    /**
     * @var Definition
     */
    private $predefinedService;

    /**
     * @var CallbackInfo
     */
    private $callbackInfo;

    /**
     * @var array
     */
    private $arguments = [];

    /**
     * @var array
     */
    private $defaultValues = [];


    private function __construct(CallbackInfo $callbackInfo, Definition &$predefinedService = null) {
        $this->predefinedService = $predefinedService;
        $this->callbackInfo      = $callbackInfo;
    }

    /**
     * @param CallbackInfo    $callbackInfo
     * @param Definition|null $predefinedService
     *
     * @throws ServiceDuplicationException
     */
    public static function fixDefinition(CallbackInfo $callbackInfo, Definition &$predefinedService = null) {
        $instance = new self($callbackInfo, $predefinedService);

        $instance->checkDefinitionAlreadyFixed($predefinedService, $callbackInfo);
        $instance->collectArguments();
        $instance->buildDefinition();
    }

    private function buildDefinition() {
        $service = &$this->predefinedService;

        $service->setPublic(true);
        $service->setShared(false);

        $service->replaceArgument(0, $this->getNumArguments());
        $service->replaceArgument(1, $this->getArguments());
        $service->replaceArgument(2, $this->getDefaultValues());

        // clear all predefined parameters
        for ($i = 3; $i < $this->getNumArguments(); $i++) {
            $service->replaceArgument($i, CallbackServiceRepresentation::EMPTY_VALUE);
        }
    }

    /**
     * Number of arguments for a service
     *
     * @return int
     */
    private function getNumArguments(): int {
        return $this->callbackInfo->getNumArguments();
    }

    /**
     * Return Argument list
     *
     * @return array
     */
    private function getArguments(): array {
        return $this->arguments;
    }

    /**
     * Return default value list
     *
     * @return array
     */
    private function getDefaultValues(): array {
        return $this->defaultValues;
    }


    private function collectArguments() {
        // this must be called first
        $this->collectArgumentsFromPredefinedService();

        // this must be called after collectServicePredefinedArguments()
        $this->collectArgumentsFromFunctionDeclaration();
    }

    /**
     * Extract predefined arguments from service.yml files
     */
    private function collectArgumentsFromPredefinedService() {
        if (!$this->predefinedService) {
            return;
        }

        $definition = $this->predefinedService;
        $arguments  = &$this->arguments;
        $numArgs    = $this->callbackInfo->getNumArguments();

        for ($argument_index = 0; $argument_index < $numArgs; $argument_index++) {

            // get predefined-argument-value at this index
            $predefinedValue = $this->getPredefinedArgumentValue($definition, $argument_index);

            // if PARAM/VALUE is already PreDefined (in yaml/xml files),
            // Those arguments are more important than the arguments being processing here
            // so we keep it
            if (CallbackServiceRepresentation::EMPTY_VALUE != $predefinedValue) {
                $arguments[$argument_index] = $predefinedValue;
            }
        }
    }

    private function collectArgumentsFromFunctionDeclaration() {
        $defaultValues = &$this->defaultValues;
        $arguments     = &$this->arguments;
        $numArguments  = $this->callbackInfo->getNumArguments();

        for ($argument_index = 0; $argument_index < $numArguments; $argument_index++) {

            // if there is no predefined-argument define in service file,
            // we will look for argument declared in the function-declaration
            if (!array_key_exists($argument_index, $arguments)) {

                // yes, we has info for the argument in the function-declaration at index-position
                $argument = $this->callbackInfo->getArgument($argument_index);

                if ($argument) {
                    if ($argument->isScalarType() && $argument->hasDefaultValue()) {
                        $defaultValues[$argument_index] = $argument->getDefaultValue();
                    }
                    elseif ($argument->isClass()) {
                        if ($argument->hasDefaultValue()) {
                            $arguments[$argument_index]     = new Reference($argument->getType(), 0);
                            $defaultValues[$argument_index] = $argument->getDefaultValue();
                        }
                        else {
                            $arguments[$argument_index] = new Reference($argument->getType());
                        }
                    }
                }
            }
        }
    }

    private function getPredefinedArgumentValue(Definition $definition, int $argument_index) {
        try {
            // get predefined-argument-value at this index
            $predefinedValue = $definition->getArgument($argument_index);

            return $predefinedValue;
        } catch (OutOfBoundsException $exception) {
            // OutOfBoundsException means there is no argument-definition at this index
            // so we can add our auto-wired-service
        }

        return CallbackServiceRepresentation::EMPTY_VALUE;
    }

    /*
     * Index0: number of argument
     * Index1: array of arguments
     * Index2: array of default-values
     * Index3: Empty_Value
     *
     * if all 4 positions are set exactly as above, the service absolute already fixed
     */
    private function checkDefinitionAlreadyFixed(Definition $definition, CallbackInfo $callbackInfo) {
        $isFixed = true
                   && is_numeric($this->getPredefinedArgumentValue($definition, 0))
                   && is_array($this->getPredefinedArgumentValue($definition, 1))
                   && is_array($this->getPredefinedArgumentValue($definition, 2))
                   && $this->getPredefinedArgumentValue($definition, 3) === CallbackServiceRepresentation::EMPTY_VALUE;

        if ($isFixed) {
            throw new ServiceDuplicationException($callbackInfo);
        }
    }
}