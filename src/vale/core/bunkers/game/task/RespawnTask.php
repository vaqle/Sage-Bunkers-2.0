<?php
namespace vale\core\bunkers\game\task;

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\team\structure\Team;
use vale\core\bunkers\util\TeamUtil;
use vale\core\bunkers\util\Utils;

class RespawnTask extends Task{

	public int $time = 30;

	public function __construct(
		public Player $player,
		public Team $team,
	){
		$player->setHealth(20);
		TeamUtil::setImmutable($this->getPlayer());
	}

	public function getPlayer(): Player{
		return $this->player;
	}

	public function getTeam(): Team{
		return $this->team;
	}

	public function getTime(): int{
		return $this->time;
	}

	public function setTime(int $time): void
	{
		$this->time = $time;
	}

	public function onRun(): void
	{
		if($this->getTime() >= 0){
			Utils::hidePlayer($this->getPlayer());
			$this->setTime($this->getTime() - 1);
		}
		$this->getPlayer()->getXpManager()->setXpLevel($this->getTime());
		if($this->getTime() <= 0){
			Utils::showPlayer($this->getPlayer());
			Bunkers::getInstance()->getProfileManager()->getProfile($this->getPlayer())->setRespawning(false);
			$this->getTeam()->respawnPlayer($this->getPlayer());
			$this->getPlayer()->getXpManager()->setXpLevel(0);
			$this->getHandler()->cancel();
		}
	}
}