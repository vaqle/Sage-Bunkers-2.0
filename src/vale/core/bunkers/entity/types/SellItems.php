<?php
namespace vale\core\bunkers\entity\types;

use form\Utils;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\entity\shop\menu\types\SellMenu;
use vale\core\bunkers\entity\shop\type\ShopType;
use vale\core\bunkers\team\structure\Team;
use vale\core\bunkers\team\TeamManager;

class SellItems extends Entity
{
	public ?Team $team = null;
	public int $shopType;

	public function __construct(Location $location, ?Team $team = null, ?CompoundTag $nbt = null)
	{
		parent::__construct($location, $nbt);
		if($team === null){
			$this->flagForDespawn();
			return;
		}
		$this->team = $team;
		$this->shopType = ShopType::SELL;
		$this->setNameTagAlwaysVisible();
		$this->setFallDistance(0);
		$this->setImmobile();
		$this->setNameTag(TeamManager::$formats[$team->getName()] . " Sell Items");
	}

	public function getTeam(): ?Team{
		return $this->team;
	}

	public function attack(EntityDamageEvent $source): void
	{
		if($source instanceof EntityDamageByEntityEvent) {
			$damaged = $source->getDamager();
			if ($damaged instanceof Player) {
				new SellMenu($damaged);
			}
		}
	}

	public function getShopType(): int{
		return $this->shopType;
	}

	protected function getInitialSizeInfo(): EntitySizeInfo
	{
		return new EntitySizeInfo(1.5, 1, 0.6);
	}

	public static function getNetworkTypeId(): string
	{
		return EntityIds::VILLAGER;
	}

	public function getName(): string
	{
		return "SellItems";
	}
}