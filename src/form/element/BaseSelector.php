<?php

declare(strict_types=1);

namespace form\element;

use http\Exception\InvalidArgumentException;
use pocketmine\form\FormValidationException;

abstract class BaseSelector extends CustomFormElement
{

    public function __construct(
        private string $name,
        private string $text,
        protected array $options,
        protected int $defaultOptionIndex = 0
    ){
        parent::__construct($name, $text);
        $this->options = array_values($options);
        if(!isset($this->options[$defaultOptionIndex])) {
            throw new InvalidArgumentException("No option at index $defaultOptionIndex, cannot set as default");
        }
    }

    public function validateValue($value): void
    {
        if(!is_int($value)) {
            throw new FormValidationException("Expected int, got " . gettype($value));
        }
        if(!isset($this->options[$value])) {
            throw new FormValidationException("Option $value does not exist");
        }
    }

    public function getOption(int $index): ?string
    {
        return $this->options[$index] ?? null;
    }

    public function getDefaultOptionIndex(): int
    {
        return $this->defaultOptionIndex;
    }

    public function getDefaultOption(): string
    {
        return $this->options[$this->defaultOptionIndex];
    }

    public function getOptions(): array
    {
        return $this->options;
    }
}