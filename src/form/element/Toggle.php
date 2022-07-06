<?php

declare(strict_types=1);

namespace form\element;

use pocketmine\form\FormValidationException;

class Toggle extends CustomFormElement
{
    public function __construct(
        string $name,
        string $text,
        private bool $default = false) {
        parent::__construct($name, $text);
    }

    public function getType(): string
    {
        return "toggle";
    }

    public function getDefaultValue(): bool
    {
        return $this->default;
    }

    public function validateValue($value): void
    {
        if(!is_bool($value)) {
            throw new FormValidationException("Expected bool, got " . gettype($value));
        }
    }

    protected function serializeElementData(): array
    {
        return [
            "default" => $this->default
        ];
    }
}