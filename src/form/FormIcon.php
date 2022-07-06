<?php

declare(strict_types=1);

namespace form;

use JsonSerializable;

class FormIcon implements JsonSerializable
{
    public const IMAGE_TYPE_URL = "url";

    public const IMAGE_TYPE_PATH = "path";

    public function __construct(
        private string $data,
        private string $type = self::IMAGE_TYPE_URL
    ){}

    public function getType(): string
    {
        return $this->type;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function jsonSerialize()
    {
        return [
            "type" => $this->type,
            "data" => $this->data
        ];
    }
}