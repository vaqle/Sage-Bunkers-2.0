<?php

declare(strict_types=1);

namespace form\element;

class Dropdown extends BaseSelector
{
    public function getType(): string
    {
        return "dropdown";
    }

    protected function serializeElementData(): array
    {
        return [
            "options" => $this->options,
            "default" => $this->defaultOptionIndex
        ];
    }
}