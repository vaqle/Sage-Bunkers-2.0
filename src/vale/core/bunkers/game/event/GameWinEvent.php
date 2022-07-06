<?php
namespace vale\core\bunkers\game\event;

use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use vale\core\bunkers\game\Game;
use vale\core\bunkers\team\structure\Team;

class GameWinEvent extends Event implements Cancellable{

	const KOTH = 0;

	const TDM = 1;

	public Game $game;

	public Team $winningTeam;

	public array $profiles;

	public array $teams;

	public int $reason = self::KOTH;

	public function __construct(Game $game, Team $winningTeam, array $profiles, array $teams, int $reason = self::KOTH){
		$this->game = $game;
		$this->winningTeam = $winningTeam;
		$this->profiles = $profiles;
		$this->teams = $teams;
		$this->reason = $reason;
	}

	public function getGame(): Game{
		return $this->game;
	}

	public function getWinningTeam(): Team{
		return $this->winningTeam;
	}

	public function getProfiles(): array{
		return $this->profiles;
	}

	public function getTeams(): array{
		return $this->teams;
	}

	public function getReason(): int{
		return $this->reason;
	}

	public function isCancelled(): bool
	{
		return false;
	}
}