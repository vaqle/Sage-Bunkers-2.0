<?php

declare(strict_types=1);

namespace form;

use InvalidArgumentException;

class CustomFormResponse {

    public function __construct(
        private array $data
    ) {}

    public function getInt(string $name): int
    {
        $this->checkExists($name);
        return $this->data[$name];
    }

    public function getString(string $name): string
    {
        $this->checkExists($name);
        return $this->data[$name];
    }

    public function getFloat(string $name): float
    {
        $this->checkExists($name);
        return $this->data[$name];
    }

    public function getBool(string $name): bool
    {
        $this->checkExists($name);
        return $this->data[$name];
    }

    public function getAll(): array
    {
        return $this->data;
    }

    private function checkExists(string $name): void
    {
        if(!isset($this->data[$name])) {
            throw new InvalidArgumentException("Value \"$name\" not found");
        }
    }
}