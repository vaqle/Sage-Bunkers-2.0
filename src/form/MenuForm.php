<?php

declare(strict_types=1);

namespace form;

use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

abstract class MenuForm extends BaseForm
{
    public function __construct(
        string $title,
        private string $content,
        private array $options
    ){
        assert(Utils::validateObjectArray($options, MenuOption::class));
        parent::__construct($title);
        $this->options = array_values($options);
    }

    public function getOption(int $position): ?MenuOption
    {
        return $this->options[$position] ?? null;
    }

    public function onSubmit(Player $player, int $selectedOption): void {}

    public function onClose(Player $player): void {}

    final public function handleResponse(Player $player, $data): void {
        if($data === null) {
            $this->onClose($player);
        }
        elseif(is_int($data)) {
            if(!isset($this->options[$data])) {
                throw new FormValidationException("Option $data does not exist");
            }
            $this->onSubmit($player, $data);
        }
        else {
            throw new FormValidationException("Expected int or null, got " . gettype($data));
        }
    }

    protected function getType(): string
    {
        return "form";
    }

    protected function serializeFormData(): array
    {
        return [
            "content" => $this->content,
            "buttons" => $this->options
        ];
    }
}