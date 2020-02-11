<?php
namespace D7ServiceContainer\DependencyInjection\DrupalCodeAnalyzer;


use Generator;

interface DrupalCallbackAnalyzerInterface {
    const ANNOTATION = 'DrupalCallbackService';

    public function getCallbacks(): Generator;
}