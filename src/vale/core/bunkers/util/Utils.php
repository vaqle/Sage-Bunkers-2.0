<?php

namespace vale\core\bunkers\util;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\Sword;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\sound\AnvilUseSound;
use vale\core\bunkers\entity\types\CombatEntity;
use vale\core\bunkers\entity\types\SellItems;
use vale\core\bunkers\team\structure\Team;
use WolfDen133\WFT\Texts\FloatingText;

class Utils {

	public static function recursiveCopy(string $source, string $target): void
	{
		$dir = opendir($source);
		@mkdir($target);
		while ($file = readdir($dir)) {
			if ($file === "." || $file === "..") {
				continue;
			}
			if (is_dir($source . DIRECTORY_SEPARATOR . $file)) {
				self::recursiveCopy($source . DIRECTORY_SEPARATOR . $file, $target . DIRECTORY_SEPARATOR . $file);
			} else {
				copy($source . DIRECTORY_SEPARATOR . $file, $target . DIRECTORY_SEPARATOR . $file);
			}
		}
		closedir($dir);
	}

	public static function closeTo(FloatingText $text){
		foreach (Server::getInstance()->getOnlinePlayers() as $player){
			$text->closeTo($player);
		}
	}

	public static function spawnTo(FloatingText $text){
		foreach (Server::getInstance()->getOnlinePlayers() as $player){
			$text->spawnTo($player);
		}
	}

	public static function changeTo(FloatingText $text){
		foreach (Server::getInstance()->getOnlinePlayers() as $player){
			$text->updateTextTo($player);
		}
	}

	public static function getNewEntityFromName(string $entity, Location $position, Team $team): Entity{
		return match ($entity){
			"Combat Shop" => new CombatEntity($position,$team)
		};
	}

	public static function hidePlayer(Player $player): void{
		array_map(function (Player $players) use ($player) {
			$players->hidePlayer($player);
		}, Server::getInstance()->getOnlinePlayers());
	}

	public static function showPlayer(Player $player): void{
		array_map(function (Player $players) use ($player) {
			$players->showPlayer($player);
		}, Server::getInstance()->getOnlinePlayers());
	}

	public static function isMerchant(Entity $entity): bool{
		return $entity instanceof CombatEntity || $entity instanceof SellItems;
	}

	public static function isValidOre(Block $block): bool{
		return in_array($block->asItem()->getId(),[
			VanillaBlocks::IRON_ORE()->getId(),
			VanillaBlocks::DIAMOND_ORE()->getId(),
			VanillaBlocks::GOLD_ORE()->getId(),
			VanillaBlocks::COAL_ORE()->getId(),
		]);
	}

	public static function blockToOre(Block $block): Item{
		return match ($block->asItem()->getId()){
			VanillaBlocks::IRON_ORE()->getId() => VanillaItems::IRON_INGOT(),
			VanillaBlocks::DIAMOND_ORE()->getId() => VanillaItems::DIAMOND(),
			VanillaBlocks::GOLD_ORE()->getId() => VanillaItems::GOLD_INGOT(),
			VanillaBlocks::COAL_ORE()->getId() => VanillaItems::COAL(),
		};
	}

	public static function recursiveDelete(string $path): void
	{
		if (basename($path) === "." or basename($path) === "..") {
			return;
		}
		foreach (scandir($path) as $item) {
			if ($item === "." or $item === "..") {
				continue;
			}
			if (is_dir($path . DIRECTORY_SEPARATOR . $item)) {
				self::recursiveDelete($path . DIRECTORY_SEPARATOR . $item);
			}
			if (is_file($path . DIRECTORY_SEPARATOR . $item)) {
				unlink($path . DIRECTORY_SEPARATOR . $item);
			}
		}
		rmdir($path);
	}

    public static function REPAIR(Player $player): void
	{
		$items = array_combine($player->getInventory()->getContents(), $player->getArmorInventory()->getContents()); //lol bro shouldve seen the old func im so good at this
		foreach ($items as $slot => $item) {
			if ($item instanceof Armor) {
				$item->setDamage(0);
			} elseif ($item instanceof Sword) {
				$item->setDamage(0);
			}
			$player->getInventory()->setItem($slot, $item);
		}
		$player->getWorld()->addSound($player->getLocation(), new AnvilUseSound());
    }
}