<?php
namespace vale\core\bunkers\entity\shop\menu\base;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\DeterministicInvMenuTransaction;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\player\Player;

abstract class HopperMenu extends InvMenu
{
	public Player $player;

	public string $identifier = InvMenuTypeIds::TYPE_HOPPER;

	public function __construct(Player $player)
	{
		parent::__construct(InvMenuHandler::getTypeRegistry()->get($this->identifier));
		$this->player = $player;
	}

	public function load(): void
	{
	}

	public function handle(Player $player, DeterministicInvMenuTransaction $transaction): void{
	}
}