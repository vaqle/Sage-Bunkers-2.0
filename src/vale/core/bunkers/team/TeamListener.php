<?php
namespace vale\core\bunkers\team;

use pocketmine\event\block\BlockItemPickupEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\Server;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\game\event\MemberDeathEvent;
use vale\core\bunkers\game\event\MemberLeaveEvent;
use vale\core\bunkers\game\GameManager;
use vale\core\bunkers\game\queue\form\QueueForm;
use vale\core\bunkers\game\queue\form\VotingForm;
use vale\core\bunkers\game\queue\QueueHandler;
use vale\core\bunkers\game\status\GameStatus;

class TeamListener implements Listener
{

	public function __construct(
		public Bunkers $bunkers
	)
	{}

	/**
	 * @param EntityDamageByEntityEvent $event
	 * @return void
	 */
	public function onPlayerDeath(EntityDamageByEntityEvent $event): void
	{
		$player = $event->getEntity();
		$killer = $event->getDamager();
		if (!$killer instanceof Player && !$player instanceof Player) {
			return;
		}
		$profile = Bunkers::getInstance()->getProfileManager()->getProfile($event->getEntity());
		if ($profile->isRespawning() || Bunkers::getInstance()->getProfileManager()->getProfile($killer)->isRespawning()) {
			$event->cancel();
			return;
		}
		if ($player->getHealth() - $event->getFinalDamage() < 0.1) {
			if (($team = $this->bunkers->getTeamManager()->getTeamByMember($player)) !== null) {
				$ev = new MemberDeathEvent($player, $team, $killer, MemberDeathEvent::PLAYER);
				$ev->call();
				$event->cancel();
			}
		}
	}

	public function onDamage(EntityDamageEvent $event): void
	{
		if (GameManager::getActiveGame() === null) {
			$event->cancel();
			return;
		}
		$profile = Bunkers::getInstance()->getProfileManager()->getProfile($event->getEntity());
		$team = Bunkers::getInstance()->getTeamManager()->getTeamByMember($event->getEntity());
		if ($team === null) $event->cancel();
		if ($profile->isRespawning()) {
			$event->cancel();
			return;
		}
		if (in_array($event->getCause(), [
			EntityDamageEvent::CAUSE_FIRE,
			EntityDamageEvent::CAUSE_DROWNING,
			EntityDamageEvent::CAUSE_SUFFOCATION
		])) {
			$event->cancel();
			return;
		}
		if (GameManager::getActiveGame()->getStatus() !== GameStatus::INGAME) {
			$event->cancel();
			return;
		}

		if ($event->getCause() === EntityDamageEvent::CAUSE_FALL && $event->getEntity()->getHealth() - $event->getFinalDamage() < 0.1) {
			$ev = new MemberDeathEvent($event->getEntity(), $team, $event->getEntity(), MemberDeathEvent::FALL);
			$ev->call();
		}
	}

	/**
	 * @param PlayerQuitEvent $event
	 * @return void
	 */
	public function onPlayerQuit(PlayerQuitEvent $event): void
	{
		$player = $event->getPlayer();

		if (($team = Bunkers::getInstance()->getTeamManager()->getTeamByMember($player)) !== null) {
			$ev = new MemberLeaveEvent($player, $team);
			$ev->call();
		} else {
			if (QueueHandler::exists($player)) {
				QueueHandler::remove($player);
			}
		}
		Bunkers::getInstance()->getProfileManager()->deleteProfile($player);
	}

	public function onDrop(PlayerDropItemEvent $event): void
	{
		if (GameManager::getActiveGame() === null) {
			$event->cancel();
			return;
		}
		if (($profile = Bunkers::getInstance()->getProfileManager()->getProfile($event->getPlayer())) !== null) {
			if ($profile->isRespawning()) {
				$event->cancel();
				return;
			}
		}
		if (GameManager::getActiveGame()->getStatus() !== GameStatus::INGAME) {
			$event->cancel();
		}
	}

	public function onTransaction(InventoryTransactionEvent $event): void
	{
		$player = $event->getTransaction()->getSource();
		if (GameManager::getActiveGame() === null) {
			$event->cancel();
			return;
		}
		if (($profile = Bunkers::getInstance()->getProfileManager()->getProfile($player)) !== null) {
			if ($profile->isRespawning()) {
				$event->cancel();
				return;
			}
		}
		if (GameManager::getActiveGame()->getStatus() !== GameStatus::INGAME) {
			$event->cancel();
		}
	}

	public function onItemPickUpEvent(EntityItemPickupEvent $event): void
	{
		$player = $event->getEntity();
		$profile = Bunkers::getInstance()->getProfileManager()->getProfile($player);
		if ($profile->isRespawning()) $event->cancel();
		else if (GameManager::getActiveGame() === null) $event->cancel();
		else if (GameManager::getActiveGame()->getStatus() !== GameStatus::INGAME) $event->cancel();
	}

	/**
	 * @param PlayerItemUseEvent $event
	 * @return void
	 * SHOUTOUT TO YOEL FOR MAKING THIS CLEANER
	 */
	public function onClick(PlayerItemUseEvent $event): void
	{
		$player = $event->getPlayer();
		$item = $event->getItem();
		if (($tag = $item->getNamedTag()->getTag("type")) !== null) {
			$event->cancel();
			$form = match (strtolower($tag->getValue())) {
				"hub" => new QueueForm($player),
				"vote" => new VotingForm($player)
			};
			$player->sendForm($form);
		} elseif (($tag = $item->getNamedTag()->getTag("team")) !== null) {
			$event->cancel();
			$teamName = ucfirst($tag->getValue());
			$team = Bunkers::getInstance()->getTeamManager()->getTeamByName($teamName);
			if (Bunkers::getInstance()->getTeamManager()->getTeamByMember($player) !== null) {
				$player->sendMessage("§r§cYou are already in a team!");
				return;
			}
			//TODO check if team is full
			$team->addMember($player);
			$player->sendMessage("§eYou have joined the " . TeamManager::$colors[$teamName] . " team§e!");
		}
	}
}