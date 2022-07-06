<?php
namespace vale\core\bunkers\team\claim;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\scheduler\ClosureTask;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\team\claim\event\PlayerEnterClaimEvent;
use vale\core\bunkers\team\TeamManager;
use vale\core\bunkers\util\TeamUtil;
use vale\core\bunkers\util\Utils;

class ClaimListener implements Listener
{
	public function onClaimEnter(PlayerEnterClaimEvent $event): void
	{
		$player = $event->getPlayer();
		$from = $event->getFromTeam();
		$to = $event->getToTeam();
		$player->sendMessage("§r§eNow leaving: " . ($from === null ? "§r§7Warzone" : TeamManager::$colors[$from->getName()] ?? $from->getName()));
		$player->sendMessage("§r§eNow entering: " . ($to === null ? "§r§7Warzone" : TeamManager::$colors[$to->getName()] ?? $to->getName()));
	}

	public function onMove(PlayerMoveEvent $event): void
	{
		$from = $event->getFrom();
		$to = $event->getTo();
		if ($from->getX() === $to->getX() and $from->getY() === $to->getY() and $from->getZ() === $to->getZ()) {
			return;
		}
		$player = $event->getPlayer();
		$fromFac = ClaimManager::getClaimAtPosition($from)?->getTeam();
		$toFac = ClaimManager::getClaimAtPosition($to)?->getTeam();
		if ($fromFac !== $toFac) {
			$event = new PlayerEnterClaimEvent($player, $from, $to, $fromFac, $toFac);
			$event->call();
		}
	}

	public function onBreak(BlockBreakEvent $event): void
	{
		$player = $event->getPlayer();
		$team = Bunkers::getInstance()->getTeamManager()->getTeamByMember($player);
		$block = $event->getBlock();
		if ($team === null) {
			$event->cancel();
			return;
		}
		if($team->isRaidable()){
			return;
		}
		$claim = ClaimManager::getClaimAtPosition($block->getPosition());
		if ($claim === null) {
			$event->cancel();
			return;
		}
		if(!Utils::isValidOre($block) && !TeamUtil::equalsTeam($team, $claim->getTeam())) {
			$event->cancel();
			return;
		}
		if (Utils::isValidOre($block)) {
			$team->addToBlocksQueue($block);
			Bunkers::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($block) {
				$block->getPosition()->getWorld()->setBlock($block->getPosition()->asVector3(), VanillaBlocks::COBBLESTONE());
			}), 5);
			$player->getInventory()->addItem(Utils::blockToOre($block));
			$event->setDrops([]);
		}
	}

	/**
	 * @param PlayerInteractEvent $event
	 * @return void
	 */
	public function extracted(PlayerInteractEvent $event): void
	{
		$player = $event->getPlayer();
		$team = Bunkers::getInstance()->getTeamManager()->getTeamByMember($player);
		if ($team === null) {
			$event->cancel();
			return;
		}
		$claim = ClaimManager::getClaimAtPosition($player->getPosition());
		if ($claim === null) {
			$event->cancel();
			return;
		}
		if (!TeamUtil::equalsTeam($claim->getTeam(), $team) && !$claim->getTeam()->isRaidable()) {
			$event->cancel();
		}
	}
}