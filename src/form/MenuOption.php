<?php

declare(strict_types=1);

namespace form;

use JsonSerializable;

class MenuOption implements JsonSerializable
{
    public function __construct(
        private string $text,
        private ?FormIcon $image = null
    ){}

    public function getText(): string
    {
        return $this->text;
    }

    public function hasImage(): bool
    {
        return $this->image !== null;
    }

    public function getImage(): ?FormIcon
    {
        return $this->image;
    }

    public function jsonSerialize()
    {
        $json = [
            "text" => $this->text
        ];
        if($this->hasImage()) {
            $json["image"] = $this->image;
        }
        return $json;
    }
}