<?php

declare(strict_types=1);

namespace form\element;

class Label extends CustomFormElement
{
    public function getType(): string
    {
        return "label";
    }

    public function validateValue($value): void
    {
        assert($value === null);
    }

    protected function serializeElementData(): array
    {
        return [];
    }
}