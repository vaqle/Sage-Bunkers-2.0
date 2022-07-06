<?php
namespace vale\core\bunkers\profile\structure;

use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\game\GameManager;
use vale\core\bunkers\game\perks\Perk;
use vale\core\bunkers\game\queue\QueueHandler;
use vale\core\bunkers\profile\scoreboard\ScoreBoardAPI;
use vale\core\bunkers\team\structure\Team;
use vale\core\bunkers\team\TeamManager;

class Profile
{
	private int $status;

	private Player $player;

	private ?ScoreBoardAPI $scoreboard;

	public bool $voted = false;

	/** @var int <$kills, $deaths, $balance, $respawntime> */
	private int $kills = 0, $deaths = 0, $balance = 0;

	public ?Perk $perk = null;

	public bool $respawning = false;

	public function __construct(Player $player)
	{
		$this->player = $player;
		$this->status = ProfileStatus::IDLE;
		$this->player->getInventory()->addItem(VanillaItems::COMPASS());
		$this->scoreboard = new ScoreBoardAPI();
		Bunkers::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
			if ($this->player->isOnline()) {
				$this->scoreboard->sendScore($this->getPlayer(), " §r§d§lSage§r§7 | Bunkers");
				$this->tick();
			} else throw new CancelTaskException();
		}), 20);
	}

	public function tick(): void
	{
		$game = GameManager::getGameFromPlayer($this->getPlayer());
		$team = Bunkers::getInstance()->getTeamManager()->getTeamByMember($this->getPlayer());
		if (($scoreboard = $this->getScoreboard()) instanceof ScoreBoardAPI && $this->getProfileStatus() === ProfileStatus::IDLE) {
			$scoreboard->setLines($this->getPlayer(), [
				" §r",
				"   §r§dIn Lobby: §r§7" . count(Server::getInstance()->getOnlinePlayers()),
				"   §r§dIn Queue: §r§7" . count(QueueHandler::getAll()),
				" §e",
			]);
		} elseif ($this->getProfileStatus() === ProfileStatus::VOTING) {
			$array = [];
			$maps = Bunkers::getInstance()->getMapManager()->getMaps();
			$lines = [
				" §r",
				" §r§6 Team: " . ($team instanceof Team ? TeamManager::$colors[$team->getName()] : "§r§c(x)"),
				" §e",
				" §r§6§lMap Votes:",
			];
			foreach ($maps as $map) {
				$array[] = "  §r§7- " . ucfirst($map->getName()) . ": §r§a" . $game->mapVotes[$map->name];
			}
			foreach ($array as $line) {
				$lines[] = $line;
			}
			$lines[] = " §a";
			$lines[] = " §r§7use /vote <map> to vote";
			$lines[] = "§d";
			$lines[] = "  §r§6Voting Ends in: §r§6" . gmdate("i:s", $game->mapVoteTime) ?? "00:00";
			$lines[] = " §e";
			$i = 0;
			foreach ($lines as $key) {
				$i++;
				$scoreboard->setLine($this->getPlayer(), $i, $key);
			}
		} elseif ($this->getProfileStatus() === ProfileStatus::STARTING) {
			$scoreboard->setLines($this->getPlayer(), [
				" §r",
				" §r§6 Team: " . ($team instanceof Team ? TeamManager::$colors[$team->getName()] : "§r§c~"),
				" §r§6 Map: §r§a" . GameManager::getGameFromPlayer($this->getPlayer())->getMap()->getName(),
				" §e",
				" §r§6 Starts in: §r§6" . gmdate("i:s", $game->countDownTime) ?? "00:00",
				" §e",
			]);
		} elseif ($this->getProfileStatus() === ProfileStatus::PLAYING) {
			$koth = null;
			$map = $game->getMap();
			if ($game->getKothGame() !== null) {
				$koth = $game->getKothGame();
			}
			$this->getPlayer()->setNameTag(TeamManager::$formats[$team->getName()] . "{$this->player->getName()}");
			$lines = [];
			$lines[] = " §r";
			$lines[] = " §r§6§l Game Time: §r§c" . gmdate("i:s", $game->getRuntime());
			$lines[] = " §r§e§l Team: §r" . ($team instanceof Team ? TeamManager::$colors[$team->getName()] : "§r§c(x)");
			$lines[] = " §r§4§l DTR: §r§c{$team->getDtr()}";
			if ($koth !== null) {
				$time = gmdate("i:s", $game->getKothGame()->getCapTime());
				$lines[] = " §r§6§l {$koth->getName()}: §r§c$time";
			}
			$balance = "$" . number_format($this->getMoney());
			$lines[] = " §r§a§l Balance: §r§c{$balance}";
			$lines[] = " §e";
			$points = 0;
			foreach ($game->getTeams() as $team) {
				if ($team->getName() !== $map->getName() && $team->isAlive() && $koth !== null && $koth->getKothCaptures($team) > 0 && $points !== 1) {
					$points = 1;
				}
			}
			if ($points === 1) {
				foreach ($koth->getTeamCapturesasList() as $message) {
					$lines[] = " $message";
				}
				$lines[] = " §4";
			}
			$i = 0;
			foreach ($lines as $key) {
				$i++;
				$scoreboard->setLine($this->getPlayer(), $i, $key);
			}
		}
	}

	public function init(): void
	{
		$this->getPlayer()->setGamemode(GameMode::SURVIVAL());
		$this->getPlayer()->getArmorInventory()->clearAll();
		$this->getPlayer()->getInventory()->clearAll();
		$this->getPlayer()->setHealth(20);
		$this->getPlayer()->getHungerManager()->setFood(20);
		if (GameManager::getActiveGame() !== null) {
			GameManager::getActiveGame()->addSpectator($this->getPlayer());
		}else{
			$this->giveHub();
		}
	}

	public function getPerk(): ?Perk
	{
		return $this->perk;
	}

	public function setPerk(?Perk $perk): void
	{
		$this->perk = $perk;
	}

	public function setRespawning(bool $respawning): void
	{
		$this->respawning = $respawning;
	}

	public function isRespawning(): bool
	{
		return $this->respawning;
	}

	public function setStatus(int $status): void
	{
		$this->status = $status;
	}

	public function canVote(): bool
	{
		return $this->voted !== true;
	}

	public function setVoted(bool $voted): void
	{
		$this->voted = $voted;
	}

	public function getProfileStatus(): int
	{
		return $this->status;
	}

	public function getMoney(): int
	{
		return $this->balance;
	}

	public function setMoney(int $money): void
	{
		$this->balance = $money;
	}

	public function addMoney(int $money): void
	{
		$this->balance += $money;
	}

	public function getScoreBoard(): ScoreBoardAPI
	{
		return $this->scoreboard;
	}

	public function getPlayer(): Player
	{
		return $this->player;
	}

	public function reset(): void
	{
		$this->giveHub();
		$this->kills = 0;
		$this->deaths = 0;
		$this->balance = 0;
		$this->voted = false;
		$this->respawning = false;
	}

	public function giveVoting(): void
	{
		$this->setStatus(ProfileStatus::VOTING);
		$red = VanillaBlocks::WOOL()->setColor(DyeColor::RED())->asItem();
		$red->setCustomName("§r§cRed Team");
		$red->getNamedTag()->setString("team", "red");
		$this->getPlayer()->getInventory()->setItem(1, $red);
		$blue = VanillaBlocks::WOOL()->setColor(DyeColor::BLUE())->asItem();
		$blue->setCustomName("§r§9Blue Team");
		$blue->getNamedTag()->setString("team", "blue");
		$this->getPlayer()->getInventory()->setItem(2, $blue);
		$green = VanillaBlocks::WOOL()->setColor(DyeColor::GREEN())->asItem();
		$green->setCustomName("§r§2Green Team");
		$green->getNamedTag()->setString("team", "green");
		$yellow = VanillaBlocks::WOOL()->setColor(DyeColor::YELLOW())->asItem();
		$yellow->setCustomName("§r§eYellow Team");
		$yellow->getNamedTag()->setString("team", "yellow");
		$this->getPlayer()->getInventory()->setItem(3, $green);
		$this->getPlayer()->getInventory()->setItem(4, $yellow);
		$v = VanillaItems::NETHER_STAR()->setCustomName("§r§d§lVoting");
		$v->setLore([
			"§r§7Click to vote for your favorite",
			"§r§7maps!",
			"§r§7You are limited to §a1 vote§r§7 per player.",
		]);
		$v->getNamedTag()->setString("type", "vote");
		$this->getPlayer()->getInventory()->setItem(0, $v);
	}

	public function giveKit(): void{
		$this->getPlayer()->setHealth(20);
		$this->getPlayer()->getHungerManager()->setFood(20);
		$this->getPlayer()->getInventory()->clearAll();
		$this->getPlayer()->getArmorInventory()->clearAll();
		$this->getPlayer()->getInventory()->setItem(0, VanillaItems::STONE_PICKAXE());
	}

	public function addKill(): void
	{
		$this->kills++;
	}

	public function getKills(): int
	{
		return $this->kills;
	}

	public function addDeath(): void
	{
		$this->deaths++;
	}

	public function getDeaths(): int
	{
		return $this->deaths;
	}

	public function giveHub(): void
	{
		$this->getPlayer()->setNameTag($this->getPlayer()->getName());
		$this->getPlayer()->teleport(Bunkers::getInstance()->getMapManager()->getLobbyWorld()->getSpawnLocation());
		$bunkers = VanillaItems::NETHER_STAR()->setCustomName("§r§d§lBunkers");
			$bunkers->setLore([
				"§r§7Click to manage your preferences",
				"§r§7perks and options, to create a party press 'party'",
				'',
				"§r§7Max players: §a5",
			]);
		$bunkers->getNamedTag()->setString("type", "hub");
		$this->getPlayer()->getInventory()->setItem(0, $bunkers);
		$this->setStatus(ProfileStatus::IDLE);
	}
}