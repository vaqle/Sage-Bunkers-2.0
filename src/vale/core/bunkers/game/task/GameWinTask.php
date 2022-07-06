<?php
namespace vale\core\bunkers\game\task;

use pocketmine\scheduler\Task;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\game\Game;
use vale\core\bunkers\game\GameManager;
use vale\core\bunkers\team\claim\ClaimManager;
use vale\core\bunkers\team\structure\Team;
use vale\core\bunkers\team\TeamManager;
use vale\core\bunkers\util\TeamUtil;
use vale\core\bunkers\util\Utils;

class GameWinTask extends Task
{
	public Game $game;
	public Team $team;

	public function __construct(Game $game, Team $team)
	{
		$this->game = $game;
		$this->team = $team;
	}

	public function getGame(): Game{
		return $this->game;
	}

	public function getTeam(): Team{
		return $this->team;
	}

	public int $ticks = 0;

	public function onRun(): void
	{
		$this->ticks++;
		//TODO do fireworks on the winning team players
		foreach ($this->getGame()->getSpectators() as $spectator) {
			Utils::hidePlayer($spectator);
			TeamUtil::setImmutable($spectator);
		}
		if ($this->ticks <= 15) {
			Bunkers::getInstance()->getServer()->broadcastMessage(
				str_replace("{team}", TeamManager::$colors[$this->getTeam()->getName()], "{team} §r§ewins!"));
		} else {
			array_map(function (Team $team): void {
				$team->reset();
			}, $this->getGame()->getTeams());
			foreach ($this->getGame()->getMap()->getWorld()->getEntities() as  $entity){
				if(Utils::isMerchant($entity)){
					$entity->flagForDespawn();
				}
			}
			$this->getGame()->setSpectators();
			GameManager::removeGame();
			ClaimManager::$claims = [];
			$this->getHandler()->cancel();
		}
	}
}