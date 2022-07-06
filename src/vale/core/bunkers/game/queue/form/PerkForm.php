<?php
namespace vale\core\bunkers\game\queue\form;

use form\MenuForm;
use pocketmine\player\Player;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\game\perks\PerkManager;

class PerkForm extends MenuForm{

	public function __construct(){
		$options = [];
		foreach (PerkManager::getPerks() as $perk) {
			$options[] = $perk->getName();
		}
		parent::__construct("Perks", "Select a Perk", $options);
	}

	public function onSubmit(Player $player, int $selectedOption): void{
		$perk = PerkManager::getPerk($this->getOption($selectedOption)->getText());
		if($perk === null){
			return;
		}
		$profile = Bunkers::getInstance()->getProfileManager()->getProfile($player);
		if($profile->getPerk() !== null){
			$profile->setPerk(null);
		}
		$profile->setPerk($perk);
	}
}