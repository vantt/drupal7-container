<?php

namespace D7ServiceContainer;

use Symfony\Contracts\EventDispatcher\Event;

class DrupalEvent extends Event {
    protected $name;
    protected $data;

    /**
     * @param string $name
     * @param        $data
     */
    public function __construct(string $name, $data) {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->data;
    }
}