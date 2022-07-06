<?php
namespace vale\core\bunkers\game;

use pocketmine\player\Player;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\game\event\GameStatusChangeEvent;
use vale\core\bunkers\game\event\GameWinEvent;
use vale\core\bunkers\game\koth\KothGame;
use vale\core\bunkers\game\map\Map;
use vale\core\bunkers\game\queue\QueueHandler;
use vale\core\bunkers\game\status\GameStatus;
use vale\core\bunkers\team\structure\Team;
use vale\core\bunkers\util\TeamUtil;
use vale\core\bunkers\util\Utils;

class Game
{
	public ?KothGame $kothGame = null;

	public ?TaskHandler $taskHandler = null;

	public static $instance = null;

	/** @var Map $map */
	private Map $map;

	/** @var Map[] */
	public $mapVotes = [];

	/** @var Team[] */
	private $teams = [];

	/** @var Player[] */
	private $spectators = [];

	/** @var Player[] */
	private $playing = [];

	public int $countDownTime = 10;

	public int $mapVoteTime = 10;

	public int $runtime = 0;

	/** @var int */
	protected int $status = GameStatus::WAITING;

	public function __construct()
	{
		self::$instance = $this;
		$maps = Bunkers::getInstance()->getMapManager()->getMaps();
		array_walk($maps, function (Map $map): void {
			$this->mapVotes[$map->getName()] = 0;
		});
	}

	public function init(): void
	{
		$queues = QueueHandler::getAll();
		$this->playing = $queues;
		$this->status = GameStatus::VOTING;
		$this->teams = Bunkers::getInstance()->getTeamManager()->getAllTeams();
		array_walk_recursive($queues, function (Player $player): void {
			Bunkers::getInstance()->getProfileManager()->getProfile($player)->giveVoting();

		});
		$this->taskHandler = Bunkers::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () {
			static $started = false;

			switch ($this->getStatus()) {
				case GameStatus::VOTING;
					$this->setMapVoteTime($this->getMapVoteTime() - 1);
					if ($this->getMapVoteTime() <= 0) {
						$event = new GameStatusChangeEvent($this, GameStatus::STARTING, $this->getStatus());
						$event->call();
					}
					break;
				case GameStatus::STARTING;
					if ($this->getCountDownTime() > 0) {
						$this->setCountDownTime($this->getCountDownTime() - 1);
						Bunkers::getInstance()->getServer()->broadcastMessage(str_replace("{time}", $this->getCountDownTime(),
							"§r§eThe game will begin in §d{time} seconds§r§e."));
					}
					if ($this->getCountDownTime() === 1 && !$started) {
						$event = new GameStatusChangeEvent($this, GameStatus::INGAME, $this->getStatus());
						$event->call();
						$started = true;
					}
					break;
				case GameStatus::INGAME:
					$this->getKothGame()?->tick();
					if ($this->getRuntime() >= 10 && $this->getKothGame() === null) {
						$this->setKothGame();
					}
					$activeTeams = function (): array {
						$teams = [];
						$raidable = match ($this->getRuntime()) {
							2000 => function (): void {
								foreach ($this->getTeams() as $team) {
									if (!$team->isRaidable()) {
										$team->setDtr(-1.1);
									}
								}
							},
							default => function (): void {
							} //LOLZ hacky xd
						};
						$raidable();
						foreach ($this->getTeams() as $team) {
							if ($team->isAlive()) {
								$teams[] = $team;
							}
						}
						return $teams;
					};

					foreach ($this->getSpectators() as $spectator) {
						Utils::hidePlayer($spectator);
						TeamUtil::setImmutable($spectator);
					}
					switch ($this->getRuntime()) {
						case 1500:
						case 1900:
							Server::getInstance()->broadcastMessage("§r§c§lAll teams will go raidable in 5 minutes.");
							break;
					}
					$alive = count($activeTeams());
					if ($alive === 1) {
						$aliveTeam = $activeTeams()[0];
						(new GameWinEvent($this, $aliveTeam, $this->getPlayers(), $this->getTeams(), GameWinEvent::TDM))->call();
						throw new CancelTaskException();
					}
					array_map(function (Team $team): void {
						if ($this->getKothGame()?->getKothCaptures($team) >= 2) {
							(new GameWinEvent($this, $team, $this->getPlayers(), $this->getTeams(), GameWinEvent::KOTH))->call();
							throw new CancelTaskException();
						}
					}, $this->getTeams());
					break;
			}
		}), 20);
	}

	public function addSpectator(Player $player): void
	{
		$this->spectators[$player->getName()] = $player;
		$player->teleport($this->getMap()?->getWorld()->getSpawnLocation());
		$player->getInventory()->clearAll();
		$player->sendMessage("§r§eYou are now a spectator§r§7: §r§celiminated");
	}

	public function isSpectator(Player $player): bool
	{
		return isset($this->spectators[$player->getName()]);
	}

	public function getSpectators(): array
	{
		return $this->spectators;
	}

	public function removeSpectator(Player $player): void
	{
		unset($this->spectators[$player->getName()]);
	}

	public function removeTeam(Team $team): void
	{
		$this->teams = array_filter($this->teams, function (Team $t) use ($team) {
			return $t->getName() !== $team->getName();
		});
	}

	public function hasPlayer(Player $player): bool
	{
		return isset($this->playing[$player->getName()]);
	}

	public function getActiveProfiles(): array
	{
		return array_intersect_key($this->playing, Bunkers::getInstance()->getProfileManager()->getProfiles());
	}

	public function removePlaying(Player $player): void
	{
		unset($this->playing[$player->getName()]);
	}

	public function getKothGame(): ?KothGame
	{
		return $this->kothGame;
	}

	public function setKothGame(): void
	{
		$this->kothGame = new KothGame($this->getMap(), $this);
	}

	public function getPlayers(): array
	{
		return $this->playing;
	}

	public function setMap(Map $map): void
	{
		$this->map = $map;
	}

	public function setRunTime(): void
	{
		$this->runtime = time();
	}

	public function getRuntime(): int
	{
		return time() - $this->runtime;
	}

	public function setCountDownTime(int $time): void
	{
		$this->countDownTime = $time;
	}

	public function getCountDownTime(): int
	{
		return $this->countDownTime;
	}

	public function setMapVoteTime(int $time): void
	{
		$this->mapVoteTime = $time;
	}

	public function getMapVoteTime(): int
	{
		return $this->mapVoteTime;
	}

	public function setStatus(int $status): void
	{
		$this->status = $status;
	}

	public function getMapWithMostVotes(bool $value): string|int
	{
		$max = max($this->mapVotes);
		if ($value) return $max;
		else return array_search($max, $this->mapVotes);
	}

	public function broadcast(string $message): void
	{
		foreach ($this->getTeams() as $team) {
			$team->announce($message);
		}
	}

	public function canJoin(): bool
	{
		return $this->status !== GameStatus::STARTING;
	}

	public function getTeams(): array
	{
		return $this->teams;
	}

	public function getStatus(): int
	{
		return $this->status;
	}

	public function getMapVotes(): array
	{
		return $this->mapVotes;
	}

	public function getMap(): Map
	{
		return $this->map;
	}

	public function setSpectators(): void
	{
		foreach ($this->getSpectators() as $spectator) {
			$profile = Bunkers::getInstance()->getProfileManager()->getProfile($spectator);
			$profile->giveHub();
			Utils::showPlayer($spectator);
			$this->removeSpectator($spectator);
		}
		$this->spectators = [];
	}
}