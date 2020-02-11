<?php

namespace D7ServiceContainer;

/**
 * Class CallbackServiceRepresentation
 *
 * @package D7ServiceContainer
 */
class CallbackServiceRepresentation {

    const PARENT_SERVICE_NAME = 'drupal_callback_abstract';
    const EMPTY_VALUE         = 'n_a';

    /**
     * @var array
     */
    private $arguments = [];

    /**
     * @var array
     */
    private $defaultValues = [];

    /**
     * @var int
     */
    private $maxArgumentIndex;


    /**
     * CallbackServiceRepresentation constructor.
     *
     * @param int   $numArguments
     *              Total number of arguments declared in a drupal-callback-function
     *
     * @param array $arguments
     *              This argument will keep all override-parameter declared in the service.yaml files
     *              $argument is a zero-based-index array.
     *              The INDEX of an element is its declared-position in the drupal-callback-function
     *
     * @param array $defaultValues
     *              All default-values declared in a drupal-callback-function will be injected here by the container.
     *              $defaultValues is an zero-based-index array.
     *              The INDEX of an element is its declared-position in the drupal-callback-function
     */
    public function __construct(int $numArguments, array $arguments, array $defaultValues) {
        $this->maxArgumentIndex = $numArguments - 1;
        $this->arguments        = $arguments;
        $this->defaultValues    = $defaultValues;
    }

    /**
     * Do execute a drupal-function will all extra-services injected
     *
     * @param       $callback_function
     *              the name of drupal callback function,
     *              usually a hook or a function with the notation DrupalInjectionCallback
     * @param array $drupal_arguments
     *              the argument injected from drupal
     *
     * @return mixed
     */
    public function execute($callback_function, array $drupal_arguments = []) {
        $params = [];

        for ($i = 0; $i <= $this->maxArgumentIndex; $i++) {
            // param in service-definition is the most importance
            if (array_key_exists($i, $this->arguments)) {
                $params[] = $this->arguments[$i];
            }
            // the param passing in by drupal
            elseif (array_key_exists($i, $drupal_arguments)) {
                $params[] = &$drupal_arguments[$i];
            }
            // and final the function-default-value that keep by service
            elseif (array_key_exists($i, $this->defaultValues)) {
                $params[] = $this->defaultValues[$i];
            }
        }

        return call_user_func_array($callback_function, $params);
    }
}