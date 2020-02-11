<?php
namespace D7ServiceContainer\DependencyInjection\DrupalCodeAnalyzer;


class ArgumentInfo {

    const UNKNOWN_VALUE = '.NA';

    const SCALA_TYPES   = [
      'string',
      'int',
      'integer',
      'float',
      'double',
      'bool',
      'boolean',
      'array',
      'resource',
      'null',
    ];

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $position;

    /**
     * @var string
     */
    private $defaultValue;

    /**
     * @var string
     */
    private $type;


    /**
     * @var bool
     */
    private $isVariadict;

    /**
     * ArgumentInfo constructor.
     *
     * @param        $name
     * @param        $position
     * @param        $type
     * @param string $defaultValue
     * @param bool   $isVariadict
     */
    public function __construct(string $name, int $position, string $type, $defaultValue = self::UNKNOWN_VALUE, $isVariadict = false) {
        $this->name         = $name;
        $this->position     = $position;
        $this->defaultValue = $defaultValue;
        $this->type         = $type;
        $this->isVariadict  = $isVariadict;
    }

    /**
     * @return mixed
     */
    public function getName(): string  {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getPosition(): int {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getDefaultValue() {
        return $this->defaultValue;
    }

    /**
     * @return bool
     */
    public function hasDefaultValue() :bool {
        return ($this::UNKNOWN_VALUE != $this->defaultValue);
    }

    /**
     * @return mixed
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isVariadict(): bool {
        return $this->isVariadict;
    }

    /**
     * @return bool
     */
    public function isScalarType(): bool {
        $type = $this->type;

        return (empty($type) || in_array(strtolower($type), self::SCALA_TYPES));
    }

    /**
     * @return bool
     */
    public function isClass() : bool {
        return !$this->isScalarType();
    }
}