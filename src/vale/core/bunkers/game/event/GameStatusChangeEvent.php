<?php
namespace vale\core\bunkers\game\event;

use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use vale\core\bunkers\game\Game;

class GameStatusChangeEvent extends Event implements Cancellable{

	public Game $game;

	public int $newStatus;

	public int $oldStatus;

	public function __construct(Game $game, int $newStatus, int $oldStatus){
		$this->game = $game;
		$this->newStatus = $newStatus;
		$this->oldStatus = $oldStatus;
	}

	public function getGame(): Game{
		return $this->game;
	}

	public function getNewStatus(): int{
		return $this->newStatus;
	}

	public function getOldStatus(): int{
		return $this->oldStatus;
	}

	public function isCancelled(): bool
	{
		return false;
	}
}