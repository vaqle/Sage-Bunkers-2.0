<?php

declare(strict_types=1);

namespace form;

use pocketmine\form\Form;

abstract class BaseForm implements Form
{
    public function __construct(
        private string $title
    ) {}

    public function getTitle(): string
    {
        return $this->title ?? "";
    }

    final public function jsonSerialize(): array
    {
        $ret = $this->serializeFormData();
        $ret["type"] = $this->getType();
        $ret["title"] = $this->getTitle();
        return $ret;
    }

    abstract protected function getType(): string;

    abstract protected function serializeFormData(): array;
}