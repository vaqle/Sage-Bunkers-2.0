<?php

declare(strict_types=1);

namespace form;

abstract class ServerSettingsForm extends CustomForm {

    public function __construct(
        string $title,
        array $elements,
        private ?FormIcon $icon = null
    ){
        parent::__construct($title, $elements);
    }

    public function hasIcon(): bool
    {
        return $this->icon !== null;
    }

    public function getIcon(): ?FormIcon
    {
        return $this->icon;
    }

    protected function serializeFormData(): array
    {
        $data = parent::serializeFormData();
        if($this->hasIcon()) {
            $data["icon"] = $this->icon;
        }
        return $data;
    }
}