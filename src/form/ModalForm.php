<?php

declare(strict_types=1);

namespace form;

use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

abstract class ModalForm extends BaseForm {

    public function __construct(
        string $title,
        private string $content,
        private string $button1 = "gui.yes",
        private string $button2 = "gui.no"
    ){
        parent::__construct($title);
    }

    public function getYesButtonText(): string
    {
        return $this->button1;
    }

    public function getNoButtonText(): string
    {
        return $this->button2;
    }

    public function onSubmit(Player $player, bool $choice): void {}

    final public function handleResponse(Player $player, $data): void
    {
        if(!is_bool($data)) {
            throw new FormValidationException("Expected bool, got " . gettype($data));
        }
        $this->onSubmit($player, $data);
    }

    protected function getType(): string
    {
        return "modal";
    }

    protected function serializeFormData(): array
    {
        return [
            "content" => $this->content,
            "button1" => $this->button1,
            "button2" => $this->button2
        ];
    }
}