<?php
namespace vale\core\bunkers\game\queue\form;

use form\FormIcon;
use form\MenuForm;
use form\MenuOption;
use pocketmine\player\Player;
use vale\core\bunkers\game\GameManager;
use vale\core\bunkers\game\queue\QueueHandler;

class QueueForm extends MenuForm
{
	public function __construct(Player $player)
	{
		if (QueueHandler::exists($player)) {
			$options[] = new MenuOption("Leave Queue\n §r§7Click to leave the queue.", new FormIcon("textures/items/iron_sword",FormIcon::IMAGE_TYPE_PATH));
		}else $options[] = new MenuOption("Join Queue\n §r§7Click to join the queue.", new FormIcon("textures/items/diamond_sword",FormIcon::IMAGE_TYPE_PATH));
		$options[] = new MenuOption("Perks\n §r§7Click to manage perks.", new FormIcon("textures/items/blaze_powder",FormIcon::IMAGE_TYPE_PATH));
		parent::__construct("§r§d§lBunkers", join("\n",[
			"§r§7Manage your perks and join the queue.",
			"§r§7You may also create a party (MAX §r§a5 §r§7players).",
			"§r§7All perks are unlocked by default.",
		]), $options);
	}

	public function onSubmit(Player $player, int $selectedOption): void
	{
		$option = explode("\n", $this->getOption($selectedOption)->getText());
		if(GameManager::getActiveGame() !== null) {
			$player->sendMessage("§r§cThere is already an active game. However you can spectate it.");
			return;
		}
		if ($option[0] === "Leave Queue") {
			QueueHandler::remove($player);
			$player->sendMessage("§r§cYou successfully left the queue for Bunkers.");
		} else {
			if ($option[0] === "Join Queue") {
				$player->sendMessage("§r§aYou successfully joined the queue for Bunkers.");
				QueueHandler::add($player);
			}else{
				//TODO: Perk
			}
		}
	}
}