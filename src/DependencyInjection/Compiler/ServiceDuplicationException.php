<?php

namespace D7ServiceContainer\DependencyInjection\Compiler;

use Exception;
use D7ServiceContainer\DependencyInjection\DrupalCodeAnalyzer\CallbackInfo;

class ServiceDuplicationException extends Exception {

    /**
     * ServiceDuplicationException constructor.
     *
     * @param CallbackInfo $callbackInfo
     */
    public function __construct(CallbackInfo $callbackInfo) {
        parent::__construct(sprintf("Service [%s] duplicated at file [%s]", $callbackInfo->getFunctionName(), $callbackInfo->getFileName()));
    }
}