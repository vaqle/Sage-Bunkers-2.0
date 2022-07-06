<?php
namespace vale\core\bunkers\game\perks;

use pocketmine\event\Listener;
use vale\core\bunkers\game\perks\event\PerkActivateEvent;

class PerkListener implements Listener{

	public function onActivation(PerkActivateEvent $event): void
	{
		$player = $event->getPlayer();
		$perk = $event->getPerk();
		switch ($perk->getType()) {
			case Perk::ATTACK:
				break;
			case Perk::BREAK:
				break;
			case Perk::GIVE:
				$item = $perk->getItem();
				$player->getInventory()->addItem($item);
				break;

		}
	}
}