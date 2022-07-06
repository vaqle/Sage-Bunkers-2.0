<?php
namespace vale\core\bunkers\entity\shop\menu\types;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\entity\shop\menu\base\HopperMenu;
use vale\core\bunkers\entity\shop\ShopManager;

class SellMenu extends HopperMenu
{
	public function __construct(Player $player)
	{
		parent::__construct($player);
		$this->load();
		$this->setListener(InvMenu::readonly(function (DeterministicInvMenuTransaction $transaction) {
			$this->handle($transaction->getPlayer(), $transaction);
		}));
		$this->send($player);
	}

	public function load(): void
	{
		$this->setName(TextFormat::BOLD . TextFormat::RED . "Sell Shop");
		foreach (ShopManager::getEntriesByIndex("Sell") as $entry) {
			$this->getInventory()->setItem($entry->getSlot(), $entry->asSell($this->player));
		}
	}


	public function handle(Player $player, DeterministicInvMenuTransaction $transaction): void
	{
		$entry = ShopManager::fromItem($transaction->getItemClicked());
		if($entry !== null) {
			foreach ($player->getInventory()->getContents() as $slot => $item){
				if($item->getId() === $transaction->getItemClicked()->getId()){
					$item->setCount($item->getCount() - 1);
					$player->getInventory()->setItem($slot, $item);
					ShopManager::sell(Bunkers::getInstance()->getProfileManager()->getProfile($player), $entry);
				}
			}
		}
	}
}