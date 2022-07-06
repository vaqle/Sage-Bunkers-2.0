<?php
namespace vale\core\bunkers\game\event;

use pocketmine\event\Cancellable;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;
use vale\core\bunkers\team\structure\Team;

class MemberLeaveEvent extends PlayerEvent implements Cancellable{

	public $player;

	public Team $team;

	public function __construct(Player $player, Team $team){
		$this->player = $player;
		$this->team = $team;
	}

	public function getPlayer(): Player{
		return $this->player;
	}

	public function getTeam(): Team{
		return $this->team;
	}

	public function isCancelled(): bool
	{
		return false;
	}
}