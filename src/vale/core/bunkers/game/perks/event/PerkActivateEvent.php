<?php
namespace vale\core\bunkers\game\perks\event;

use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\player\Player;
use vale\core\bunkers\game\perks\Perk;

class PerkActivateEvent extends Event implements Cancellable{

	public Player $player;

	public Perk $perk;

	public function __construct(Player $player, Perk $perk){
		$this->player = $player;
		$this->perk = $perk;
	}

	public function getPlayer(): Player{
		return $this->player;
	}

	public function getPerk(): Perk{
		return $this->perk;
	}

	public function isCancelled(): bool
	{
		return false;
	}
}