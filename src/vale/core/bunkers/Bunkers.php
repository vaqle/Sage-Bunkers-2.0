<?php
declare(strict_types = 1);
namespace vale\core\bunkers;

use muqsit\invmenu\InvMenuHandler;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;
use vale\core\bunkers\entity\shop\ShopManager;
use vale\core\bunkers\entity\types\CombatEntity;
use vale\core\bunkers\entity\types\SellItems;
use vale\core\bunkers\game\GameListener;
use vale\core\bunkers\game\map\MapManager;
use vale\core\bunkers\profile\ProfileManager;
use vale\core\bunkers\team\claim\ClaimListener;
use vale\core\bunkers\team\TeamListener;
use vale\core\bunkers\team\TeamManager;

class Bunkers extends PluginBase
{
	/** @var Bunkers $instance */
	private static Bunkers $instance;

	private TeamManager $teamManager;

	private MapManager $mapManager;

	private ProfileManager $profileManager;

	const PLAYERS_NEEDED_TO_START = 2;

	public function onLoad(): void
	{
		self::$instance = $this;
		$this->saveDefaultConfig();
	}

	public function onEnable(): void
	{
		InvMenuHandler::register($this);
		$this->getServer()->getPluginManager()->registerEvent(PlayerJoinEvent::class, function (PlayerJoinEvent $event): void {
			$event->setJoinMessage("");
			$this->getProfileManager()->addProfile($event->getPlayer());
		}, EventPriority::NORMAL, $this);

		$this->getServer()->getPluginManager()->registerEvent(PlayerQuitEvent::class, function (PlayerQuitEvent $event): void {
			$event->setQuitMessage("");
			$this->getProfileManager()->deleteProfile($event->getPlayer());
		}, EventPriority::NORMAL, $this);
		ShopManager::init();
		$entities = [CombatEntity::class, SellItems::class];
		foreach ($entities as $entity) {
			EntityFactory::getInstance()->register($entity, function (World $world, ?CompoundTag $nbt) use ($entity): Entity {
				return new $entity(EntityDataHelper::parseLocation($nbt, $world), null);
			}, [$entity]);
		}
		$this->teamManager = new TeamManager();
		$this->profileManager = new ProfileManager();
		$this->mapManager = new MapManager();
		$this->getServer()->getPluginManager()->registerEvents(new ClaimListener(), $this);
		$this->getServer()->getPluginManager()->registerEvents(new TeamListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new GameListener($this), $this);
	}

	public function getMapManager(): MapManager{
		return $this->mapManager;
	}

	public function getProfileManager(): ProfileManager{
		return $this->profileManager;
	}

	public function getTeamManager(): TeamManager{
		return $this->teamManager;
	}

	public static function getInstance(): self{
		return self::$instance;
	}
}