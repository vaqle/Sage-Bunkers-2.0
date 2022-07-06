<?php

declare(strict_types=1);

namespace form\element;

use pocketmine\form\FormValidationException;
use InvalidArgumentException;

class Slider extends CustomFormElement
{
    public function __construct(
        private string $name,
        private string $text,
        private float $min,
        private float $max,
        private float $step = 1.0,
        private ?float $default = null
    ){
        parent::__construct($name, $text);
        if($min > $max) {
            throw new InvalidArgumentException("Slider min value should be less than max value");
        }

        if($default !== null) {
            if($default > $max or $default < $min) {
                throw new InvalidArgumentException("Default must be in range $min ... $max");
            }
        } else {
            $this->default = $this->min;
        }

        if($step <= 0) {
            throw new InvalidArgumentException("Step must be greater than zero");
        }
    }

    public function getType(): string
    {
        return "slider";
    }

    public function validateValue($value): void
    {
        if(!is_float($value) and !is_int($value)) {
            throw new FormValidationException("Expected float, got " . gettype($value));
        }
        if($value < $this->min or $value > $this->max) {
            throw new FormValidationException("Value $value is out of bounds (min $this->min, max $this->max)");
        }
    }

    public function getMin(): float
    {
        return $this->min;
    }

    public function getMax(): float
    {
        return $this->max;
    }

    public function getStep(): float
    {
        return $this->step;
    }

    public function getDefault(): float
    {
        return $this->default;
    }

    protected function serializeElementData(): array
    {
        return [
            "min" => $this->min,
            "max" => $this->max,
            "default" => $this->default,
            "step" => $this->step
        ];
    }
}