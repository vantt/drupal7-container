<?php

namespace D7ServiceContainer\DependencyInjection\DrupalCodeAnalyzer;

use Symfony\Component\Finder\Finder;
use TokenReflection\Broker;
use TokenReflection\Broker\Backend\Memory;
use TokenReflection\ReflectionClass;
use TokenReflection\ReflectionFunctionBase;
use TokenReflection\ReflectionParameter;
use Generator;
use TokenReflection\Resolver;


/**
 * Class TokenReflectionCallbackAnalyzer
 *
 * This class analyze Drupal Code using Andrewsville/PHP-Token-Reflection library
 *
 * @see https://github.com/Andrewsville/PHP-Token-Reflection
 */
final class TokenReflectionCallbackAnalyzer implements DrupalCallbackAnalyzerInterface {
    private $drupal_path = [];

    /**
     * TokenStreamAnalyzer constructor.
     *
     * @param string $drupal_root_path
     */
    public function __construct(string $drupal_root_path) {
        $this->setDrupalPath($drupal_root_path);
    }

    /**
     * @return Generator
     */
    public function getCallbacks(): Generator {
        foreach ($this->getCallbackFiles() as $file_path) {

            foreach ($this->getAllCallable($file_path) as $callable) {
                $callback_info = $this->processCallable($callable);

                if (!empty($callback_info)) {
                    // function_name => function_arguments
                    yield $callback_info[0] => $callback_info[1];
                }
            }
        }
    }

    /**
     * @param string $drupal_root_path
     */
    private function setDrupalPath(string $drupal_root_path) {
        $drupal_root_path    = str_replace('//', '/', $drupal_root_path);
        $this->drupal_path[] = $drupal_root_path . '/sites/all/modules';
        $this->drupal_path[] = $drupal_root_path . '/modules';
        $this->drupal_path[] = $drupal_root_path . '/includes';
    }

    /**
     * Get all Code Files that has @DrupalCallbackService Annotation
     *
     * @return Generator
     */
    private function getCallbackFiles() {
        $annotation = $this::ANNOTATION;
        $contains   = "/@$annotation/i";

        $finder = new Finder();
        $finder->files()
               ->name('/\.module$|\.inc$|\.php$/')
               ->contains($contains)
               ->in($this->drupal_path);

        foreach ($finder as $file) {
            yield $file->getRealPath();
        }
    }

    /**
     * Get all callable(s) in a file that has @DrupalCallbackService annotation
     * (include both methods and functions)
     *
     * @param $file_path
     *
     * @return Generator
     */
    private function getAllCallable($file_path) {
        $broker = new Broker(new Memory());
        $broker->processFile($file_path);


        foreach ($broker->getFunctions() as $callable) {
            if ($callable->hasAnnotation($this::ANNOTATION)) {
                yield $callable;
            }
        }

        /** @var ReflectionClass $class_reflection */
        foreach ($broker->getClasses() as $class_reflection) {
            foreach ($class_reflection->getMethods() as $callable) {
                if ($callable->hasAnnotation($this::ANNOTATION)) {
                    yield $callable;
                }
            }
        }
    }

    /**
     * Process a callable to get its parameters type
     *
     * @param ReflectionFunctionBase $callable
     *
     * @return array
     */
    private function processCallable(ReflectionFunctionBase $callable) {

        $function_name = rtrim($callable->getPrettyName(), '()');
        $parameters    = $callable->getParameters();

        $callbackInfo      = new CallbackInfo($callable->getFileName(),$function_name, count($parameters));
        $argumentExtractor = new ArgumentExtractor();

        foreach ($parameters as $parameter) {
            $argument = $argumentExtractor->extractArgument($parameter);

            if ($argument->isClass() || $argument->getDefaultValue() != $argument::UNKNOWN_VALUE) {
                $callbackInfo->setArgument($argument->getPosition(), $argument);
            }
        }

        return [$function_name, $callbackInfo];
    }
}

