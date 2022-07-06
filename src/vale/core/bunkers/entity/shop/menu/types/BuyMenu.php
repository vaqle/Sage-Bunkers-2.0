<?php
namespace vale\core\bunkers\entity\shop\menu\types;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\entity\shop\menu\base\DoubleChestMenu;
use vale\core\bunkers\entity\shop\ShopManager;

class BuyMenu extends DoubleChestMenu
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
		$this->setName(TextFormat::BOLD . TextFormat::RED . "Combat Shop");
		foreach (ShopManager::getEntriesByIndex("Combat") as $entry) {
			$this->getInventory()->setItem($entry->getSlot(), $entry->asBuy());
		}
	}

	public function handle(Player $player, DeterministicInvMenuTransaction $transaction): void
	{
		$entry = ShopManager::fromItem($transaction->getItemClicked());
		if ($entry !== null) {
			ShopManager::buy(Bunkers::getInstance()->getProfileManager()->getProfile($player), $entry);
		}
	}
}