<?php

declare(strict_types=1);

namespace form\element;

use JsonSerializable;

abstract class CustomFormElement implements JsonSerializable
{
    public function __construct(
        private string $name,
        private string $text,
    ){}

    abstract public function getType(): string;

    public function getName(): string
    {
        return $this->name;
    }

    public function getText(): string
    {
        return $this->text;
    }

    abstract public function validateValue($value): void;

    final public function jsonSerialize(): array {
        $ret = $this->serializeElementData();
        $ret["type"] = $this->getType();
        $ret["text"] = $this->getText();
        return $ret;
    }

    abstract protected function serializeElementData(): array;
}