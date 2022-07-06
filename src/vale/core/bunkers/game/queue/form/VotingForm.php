<?php
namespace vale\core\bunkers\game\queue\form;

use form\FormIcon;
use form\MenuForm;
use form\MenuOption;
use pocketmine\player\Player;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\game\GameManager;
use vale\core\bunkers\game\queue\QueueHandler;
use WolfDen133\WFT\Form\Form;

class VotingForm extends MenuForm
{
	public function __construct(Player $player)
	{
		$options = [];
		foreach (Bunkers::getInstance()->getMapManager()->getMaps() as $map) {
			$options[] = new MenuOption($map->getName(), new FormIcon($map->getData()["formIcon"],FormIcon::IMAGE_TYPE_PATH));
		}
		parent::__construct("§r§d§lBunkers Voting", join("\n",[
			"§r§7Vote for your favorite maps here.",
			"§r§7You may select only one map.",
			"§r§7All maps are unlocked by default.",
		]), $options);
	}

	public function onSubmit(Player $player, int $selectedOption): void
	{
		$map = Bunkers::getInstance()->getMapManager()->getMap($this->getOption($selectedOption)->getText());
		$profile = Bunkers::getInstance()->getProfileManager()->getProfile($player);
		if(!$profile->canVote()){
			$player->sendMessage("§r§cYou have already voted.");
			return;
		}
		if ($map === null) {
			$player->sendMessage("§cMap not found!");
			return;
		}
		$game = GameManager::getGameFromPlayer($player);
		if($game === null) {
			$player->sendMessage("§cYou are not in a game!");
			return;
		}
		$game->mapVotes[$map->getName()]+=1;
		$player->sendMessage("§r§aYou successfully voted for {$map->getName()}.");
		$profile->setVoted(true);
	}
}