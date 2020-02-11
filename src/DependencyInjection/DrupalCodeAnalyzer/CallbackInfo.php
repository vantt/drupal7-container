<?php

namespace D7ServiceContainer\DependencyInjection\DrupalCodeAnalyzer;


class CallbackInfo {
    private $file_name;
    private $function_name;
    private $num_arguments;
    private $arguments = [];

    /**
     * CallbackInfo constructor.
     *
     * @param string $fileName
     * @param string $function_name
     * @param int    $num_arguments
     */
    public function __construct(string $fileName, string $function_name, int $num_arguments) {
        $this->file_name     = $fileName;
        $this->function_name = $function_name;
        $this->num_arguments = $num_arguments;
    }


    /**
     * @return string
     */
    public function getServiceName(): string {
        return 'drupal_' . $this->function_name;
    }

    /**
     * @return string
     */
    public function getFunctionName(): string {
        return $this->function_name;
    }

    /**
     * @return int
     */
    public function getNumArguments(): int {
        return $this->num_arguments;
    }

    public function setArgument($index, ArgumentInfo $argumentInfo) {
        $this->arguments[$index] = $argumentInfo;
    }

    public function getArgument($index): ?ArgumentInfo {
        if (array_key_exists($index, $this->arguments)) {
            return $this->arguments[$index];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getArguments(): array {
        return $this->arguments;
    }

    /**
     * @return string
     */
    public function getFileName(): string {
        return $this->file_name;
    }
}