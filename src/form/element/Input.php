<?php

declare(strict_types=1);

namespace form\element;

use pocketmine\form\FormValidationException;

class Input extends CustomFormElement
{
     public function __construct(
        private string $name,
        private string $text,
        private string $hint = "",
        private string $default = ""
    ){
        parent::__construct($name, $text);
    }

    public function getType(): string
    {
        return "input";
    }

    public function validateValue($value): void
    {
        if(!is_string($value)) {
            throw new FormValidationException("Expected string, got " . gettype($value));
        }
    }

    public function getHintText(): string
    {
        return $this->hint;
    }

    public function getDefaultText(): string {
        return $this->default;
    }

    protected function serializeElementData(): array
    {
        return [
            "placeholder" => $this->hint,
            "default" => $this->default
        ];
    }
}