<?php
namespace vale\core\bunkers\entity\shop\entry;

use form\Utils;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use vale\core\bunkers\entity\shop\ShopManager;
use vale\core\bunkers\entity\shop\type\ShopType;

class ShopEntry{

	public string $name;

	public Item $item;

	public ?int $buyprice = null;

	public ?int $sellPrice = null;

	public int $type = ShopType::NULL;

	public int $slot = 0;

	public function __construct(string $name, Item $item, ?int $buyprice, ?int $sellPrice, int $type = ShopType::NULL, int $slot = 0){
		$this->name = $name;
		$this->item = $item;
		$this->item->getNamedTag()->setString(ShopManager::TAG_NAME, $name);
		$this->buyprice = $buyprice;
		$this->sellPrice = $sellPrice;
		$this->type = $type;
		$this->slot = $slot;
	}

	public function getName(): string{
		return $this->name;
	}

	public function getItem(): Item{
		return $this->item;
	}

	public function equals(Item $item): bool{
		if($item->getNamedTag()->getString(ShopManager::TAG_NAME,"") === ""){
			return false;
		}
		return $item->getNamedTag()->getString(ShopManager::TAG_NAME) === $this->name;
	}

	public function asSell(Player $player): Item
	{
		$item = clone $this->item;
		$count = 0;
		$price = $this->getSellPrice();
		$lore = [
			"",
			" §r§7You can sell " . strtolower($item->getVanillaName()) . "'s here",
			" §r§7They sell for $" . $price . " each.",
			"",
		];
		foreach ($player->getInventory()->getContents() as $invItem) {
			if ($invItem->getId() === $this->getItem()->getId()) {
				$count+= $invItem->getCount();
				$price += $invItem->getCount() * $this->getSellPrice();
				$lore = [
					"",
					" §r§7Left click to sell 1 x {$this->getItem()->getVanillaName()} for §r§e{$this->getSellPrice()}§r§7.",
					" §r§7Right click to sell $count x {$this->getItem()->getVanillaName()}'s for §r§e{$price}§r§7.",
					"",
				];
			}
		}
		$item->setCustomName($this->getName());
		$item->setLore($lore);
		return $item;
	}


	public function asBuy(): Item{
      $item = clone $this->item;
	  $item->setCustomName($this->getName());
	  $lore = [
		  "",
		  " §r§71x {$item->getVanillaName()}",
		  "",
		  " §r§ePrice: §r§a$" . $this->getBuyPrice(),
	  ];
	  $item->setLore($lore);
	  return $item;
	}

	public function getBuyPrice(): ?int{
		return $this->buyprice;
	}

	public function getSellPrice(): ?int{
		return $this->sellPrice;
	}

	public function getType(): int{
		return $this->type;
	}

	public function getSlot(): int{
		return $this->slot;
	}
}