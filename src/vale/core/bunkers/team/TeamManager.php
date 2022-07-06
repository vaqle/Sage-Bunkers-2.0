<?php
namespace vale\core\bunkers\team;

use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\game\Game;
use vale\core\bunkers\game\GameManager;
use vale\core\bunkers\game\queue\QueueHandler;
use vale\core\bunkers\team\structure\Team;

class TeamManager{

	/** @var array <Team $teams> */
	private array $teams = [];

	public ?TaskHandler $teamHandler = null;

	public static array $colors = [
		"Red" => "§cRed",
		"Blue" => "§9Blue",
		"Green" => "§2Green",
		"Yellow" => "§eYellow",
	];

	public static array $formats = [
		"Red" => "§c",
		"Blue" => "§9",
		"Green" => "§2",
		"Yellow" => "§e",
	];

	public function __construct(){
		$this->registerTeam(new Team("Red"));
		$this->registerTeam(new Team("Blue"));
		$this->registerTeam(new Team("Green"));
		$this->registerTeam(new Team("Yellow"));
		$this->teamHandler = Bunkers::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
			if(QueueHandler::isFull() || QueueHandler::hasEnoughToStart()){
				$g = GameManager::create(new Game());
				$g->init();
				QueueHandler::$queues = [];
			}
		}), 20);
	}

	public function registerTeam(Team $team){
		$this->teams[$team->getName()] = $team;
	}

	public function getTeamByName(string $name): ?Team{
		return $this->teams[$name] ?? null;
	}

	public function getTeamByMember(Player $member): ?Team
	{
		foreach($this->teams as $team){
			/** @var Team $team */
			if($team->hasMember($member)){
				return $team;
			}
		}
		return null;
	}

	public function getTeamWithLeastMembers(int $min): ?Team{
		$team = null;
		foreach($this->teams as $team){
			/** @var Team $team */
			if(count($team->getMembers()) < $min){
				return $team;
			}
		}
		return $team;
	}

	public function getOrderedTeams(): array{
		$array = [];
		foreach ($this->teams as $team) {
			$members = $team->getMembers();
			$array[$team->name] = count($members);
		}
		asort($array); //todo does this work?
		return $array;
	}

	public function getRandomTeam(): ?Team{
		return $this->teams[array_rand($this->teams)];
	}

	public function getAllTeams(): array{
		return $this->teams;
	}
}