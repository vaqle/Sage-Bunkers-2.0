<?php
namespace vale\core\bunkers\game\koth;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\Position;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\game\Game;
use vale\core\bunkers\game\map\Map;
use vale\core\bunkers\team\claim\Claim;
use vale\core\bunkers\team\claim\ClaimManager;
use vale\core\bunkers\team\structure\Team;
use vale\core\bunkers\team\TeamManager;

class KothGame
{
	public array $captures = [];

	public int $capRemind = 0;
	public int $capTime = 30;

	public ?Player $capturing = null;

	public function __construct(
		public Map $map,
		public Game $game
	)
	{
		foreach ($game->getTeams() as $team) {
			if ($team->getName() !== "Classic") {
				$this->captures[$team->getName()] = 0;
			}
		}
		$team = new Team($this->map->getKothData()["name"]);
		ClaimManager::registerClaim($team,new Claim($this->getMap()->getKothData()["name"],$team,$this->getMap()->convertStringToPosition($this->getMap()->getKothData()["claimpos1"]),$this->getMap()->convertStringToPosition($this->getMap()->getKothData()["claimpos2"])));
		Server::getInstance()->broadcastMessage("§r§6[KingOfTheHill] §9{$this->getName()} §r§eCan now be contested.");
	}

	public function tick(): void
	{
		foreach (Server::getInstance()->getOnlinePlayers() as $player) {
			if (($team = Bunkers::getInstance()->getTeamManager()->getTeamByMember($player)) === null) {
				continue;
			}
			if ($this->isInside($player)) {
				if ($this->getCapturing() === null) {
					$this->getGame()->broadcast("§r§6[KingOfTheHill] §9{$this->getName()} §r§eis now being controlled §r§c(0:30)");
					$this->setCapturing($player);
					$this->getCapturing()->sendMessage("§r§6[KingOfTheHill] §eAttempting to control §r§9{$this->getName()}§r§e.");
					$team->announce("§r§6[KingOfTheHill] §eYour team is controlling §r§9{$this->getName()}§r§e.");
					$this->setCapRemind(time());
				}
				if ($this->canRemind()) {
					$this->getCapturing()->sendMessage("§r§6[KingOfTheHill] §eAttempting to control §r§9{$this->getName()}§r§e.");
					$this->setCapRemind(time());
				}
				if ($this->getCapturing() !== null){
					$this->setCapTime($this->getCapTime() - 1);
				}
				if($this->getCapTime() <= 0){
					$this->addKothCaptures($team);
					$this->reset();
				}
			} else {
				if ($this->getCapturing() !== null) {
					if (!$this->isInside($this->getCapturing())) {
						$this->reset();
					}
				}
			}
		}
	}

	public function reset(): void{
		$this->setCapturing();
		$this->setCapRemind(time());
		$this->setCapTime(30);
	}

	public function getName(): string
	{
		return $this->map->getName();
	}

	public function canRemind(): bool{
		return time() - $this->capRemind >= 10;
	}

	public function getGame(): Game{
		return $this->game;
	}

	public function getCapturing(): ?Player
	{
		return $this->capturing;
	}

	public function setCapturing(?Player $player = null): void
	{
		$this->capturing = $player;
	}

	public function getCapRemind(): int
	{
		return $this->capRemind;
	}

	public function setCapRemind(int $time): void
	{
		$this->capRemind = $time;
	}

	public function getCapTime(): int
	{
		return $this->capTime;
	}

	public function setCapTime(int $time): void
	{
		$this->capTime = $time;
	}

	public function getCaptures(): array
	{
		return $this->captures;
	}

	public function addKothCaptures(Team $team): void
	{
		$this->captures[$team->getName()]++;
	}

	public function getKothCaptures(Team $team): int
	{
		return $this->captures[$team->getName()];
	}

	/***
	 * @return array
	 */
	public function getTeamCapturesAsList(): array
	{
		$list = [];
		foreach ($this->captures as $team => $captures) {
			$team = Bunkers::getInstance()->getTeamManager()->getTeamByName($team);
			if ($this->getKothCaptures($team) >= 1) {
				$list[] = " " .TeamManager::$colors[$team->getName()] . "§r§7: " . $this->getKothCaptures($team) . "/25";
			}
		}
		return $list;
	}

	public function getMap(): Map
	{
		return $this->map;
	}

	public function isInside(Player $player): bool
	{
		$position = $player->getPosition();
		$firstPosition = $this->map->convertStringToPosition($this->map->getKothData()["firstpos"]);
		$secondPosition = $this->map->convertStringToPosition($this->map->getKothData()["secondpos"]);
		$minX = min($firstPosition->getX(), $secondPosition->getX());
		$maxX = max($firstPosition->getX(), $secondPosition->getX());
		$minY = min($firstPosition->getY(), $secondPosition->getY());
		$maxY = max($firstPosition->getY(), $secondPosition->getY());
		$minZ = min($firstPosition->getZ(), $secondPosition->getZ());
		$maxZ = max($firstPosition->getZ(), $secondPosition->getZ());
		return $position->getX() >= $minX && $position->getX() <= $maxX && $position->getY() >= $minY && $position->getY() <= $maxY && $position->getZ() >= $minZ && $position->getZ() <= $maxZ;
	}
}