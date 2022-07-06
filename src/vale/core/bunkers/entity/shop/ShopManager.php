<?php
namespace vale\core\bunkers\entity\shop;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use vale\core\bunkers\entity\shop\entry\ShopEntry;
use vale\core\bunkers\entity\shop\type\ShopType;
use vale\core\bunkers\profile\structure\Profile;
use vale\core\bunkers\util\Utils;

class ShopManager
{
	public static array $entries = [];

	const TAG_NAME = "shop";

	public static function init(): void
	{
		self::$entries["Combat"] = [
			new ShopEntry(" §r§aDiamond Helmet §r§f(#0310)", VanillaItems::DIAMOND_HELMET(), 50, null, ShopType::COMBAT, 10),
			new ShopEntry(" §r§aDiamond Chestplate §r§f(#0311)", VanillaItems::DIAMOND_CHESTPLATE(), 100, null, ShopType::COMBAT, 19),
			new ShopEntry(" §r§aDiamond Leggings §r§f(#0312)", VanillaItems::DIAMOND_LEGGINGS(), 75, null, ShopType::COMBAT, 28),
			new ShopEntry(" §r§aDiamond Boots §r§f(#0313)", VanillaItems::DIAMOND_BOOTS(), 45, null, ShopType::COMBAT, 37),
			new ShopEntry(" §r§aDiamond Sword §r§f(#0314)", VanillaItems::DIAMOND_SWORD(), 125, null, ShopType::COMBAT, 18),
			new ShopEntry(" §r§aFix Durability §r§f(#0315)", VanillaItems::DIAMOND(), 300, null, ShopType::COMBAT, 20),
			new ShopEntry(" §r§aEnderpearl §r§f(#0316)", VanillaItems::ENDER_PEARL(), 25, null, ShopType::COMBAT, 21),
		];

		self::$entries["Sell"] = [
			new ShopEntry(" §r§aSell Emerald §r§f(#0263)", VanillaItems::EMERALD(), null, 120, ShopType::SELL, 0),
			new ShopEntry(" §r§bSell Diamond §r§f(#0264)", VanillaItems::DIAMOND(), null, 90, ShopType::SELL, 1),
			new ShopEntry(" §r§6Sell Gold §r§f(#0265)", VanillaItems::GOLD_INGOT(), null, 60, ShopType::SELL, 2),
			new ShopEntry(" §r§fSell Iron §r§f(#0266)", VanillaItems::IRON_INGOT(), null, 40, ShopType::SELL, 3),
			new ShopEntry(" §r§8Sell Coal §r§f(#0267)", VanillaItems::COAL(), null, 20, ShopType::SELL, 4),
		];
	}

	/**
	 * @param Item $item
	 * @return ShopEntry|null
	 */
	public static function fromItem(Item $item): ?ShopEntry
	{
		$entries = array_merge(self::$entries["Combat"], self::$entries["Sell"]);
		foreach ($entries as $entry) {
			if ($entry->equals($item)) {
				return $entry;
			}
		}
		return null;
	}

	public static function sell(Profile $profile, ShopEntry $entry): void
	{
		$profile->addMoney($entry->getSellPrice());
		$profile->getPlayer()->sendMessage("§r§aSold 1 " . $entry->getItem()->getVanillaName() . "'s for $" . $entry->getSellPrice());
	}

	public static function buy(Profile $profile, ShopEntry $entry): void
	{
		if ($profile->getMoney() < $entry->getBuyPrice()) {
			return;
		}
		$profile->setMoney($profile->getMoney() - $entry->getBuyPrice());
		if ($entry->getName() === " §r§aFix Durability §r§f(#0315)") { //any other way to do this???
			$profile->getPlayer()->sendMessage("§r§aYou have bought §r§ea" . $entry->getName() . " §r§afor §r§e$" . $entry->getBuyPrice() . "§a.");
			Utils::REPAIR($profile->getPlayer());
			return;
		}
		$profile->getPlayer()->getInventory()->addItem($entry->getItem());
		$profile->getPlayer()->sendMessage("§r§aYou have bought §r§ea " . $entry->getItem()->getVanillaName() . " §r§afor §r§e$" . $entry->getBuyPrice() . "§a.");
	}


	public static function getEntriesByIndex(string $index): array
	{
		return self::$entries[$index];
	}
}