<?php
namespace vale\core\bunkers\game\event;

use pocketmine\event\Cancellable;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;
use vale\core\bunkers\team\structure\Team;

class MemberDeathEvent extends PlayerEvent implements Cancellable{
	const PLAYER = 0;
	const FALL = 1;

	public $player;

	public $killer;

	public Team $team;

	public int $reason = self::PLAYER;

	public function __construct(Player $player, Team $team, Player $killer, int $reason){
		$this->player = $player;
		$this->team = $team;
		$this->killer = $killer;
		$this->reason = $reason;
	}

	public function getPlayer(): Player{
		return $this->player;
	}

	public function getReason(): int{
		return $this->reason;
	}

	public function getKiller(): Player{
		return $this->killer;
	}

	public function getTeam(): Team{
		return $this->team;
	}

	public function isCancelled(): bool
	{
		return false;
	}
}