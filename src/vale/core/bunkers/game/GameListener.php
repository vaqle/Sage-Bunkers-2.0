<?php
namespace vale\core\bunkers\game;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\Server;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\game\event\GameStatusChangeEvent;
use vale\core\bunkers\game\event\GameWinEvent;
use vale\core\bunkers\game\event\MemberDeathEvent;
use vale\core\bunkers\game\event\MemberLeaveEvent;
use vale\core\bunkers\game\status\GameStatus;
use vale\core\bunkers\game\task\GameWinTask;
use vale\core\bunkers\game\task\RespawnTask;
use vale\core\bunkers\profile\structure\Profile;
use vale\core\bunkers\profile\structure\ProfileStatus;
use vale\core\bunkers\team\structure\Team;
use vale\core\bunkers\team\TeamManager;
use vale\core\bunkers\util\Utils;

class GameListener implements Listener
{
	public function __construct(
		public Bunkers $bunkers
	){}

	public function onGameStatusChange(GameStatusChangeEvent $event): void
	{
		$game = $event->getGame();
		switch ($event->getOldStatus()) {
			case GameStatus::VOTING:
				$mapData = [$game->getMapWithMostVotes(false), $game->getMapWithMostVotes(true)];
				$this->getBunkers()->getServer()->broadcastMessage(str_replace([
					"{map}",
					"{votes}",
				], [$mapData[0], $mapData[1]], "§r§d{map} §r§ehas been chosen with §r§a{votes} votes§r§e"));
				$game->setMap($this->getBunkers()->getMapManager()->getMap($mapData[0]));
				array_map(function (Player $player): void {
					$this->getBunkers()->getProfileManager()->getProfile($player)->setStatus(ProfileStatus::STARTING);
				}, $game->getPlayers());
				break;
			case GameStatus::STARTING:
				$game->setRunTime();
				foreach ($game->getTeams() as $team) {
					$team->load($game->getMap());
				}
				foreach (Server::getInstance()->getOnlinePlayers() as $player) {
						if(!$game->isSpectator($player) && $this->getBunkers()->getTeamManager()->getTeamByMember($player) === null){
							$game->addSpectator($player);
						}
					}
				break;
			case GameStatus::INGAME:
				break;
		}
		$game->setStatus($event->getNewStatus());
	}

	public function onGameWin(GameWinEvent $event): void{
		$game = $event->getGame();
		$team = $event->getWinningTeam();
		switch ($event->getReason()){
			case GameWinEvent::TDM:
			case GameWinEvent::KOTH:
			$otherProfiles = array_filter($game->getActiveProfiles(), function (Player $player) use ($team): bool {
				return !$team->hasMember($player);
			});
			foreach ($otherProfiles as $player) {
				if(!$game->isSpectator($player)) $game->addSpectator($player);
			}
		}
		$this->getBunkers()->getScheduler()->scheduleRepeatingTask(new GameWinTask(
			$game,
			$team
		),20);
	}

	/**
	 * @param MemberLeaveEvent $event
	 * @return void
	 * Called when a member leaves a team.
	 */
	public function onMemberLeave(MemberLeaveEvent $event): void
	{
		$player = $event->getPlayer();
		$team = $event->getTeam();
		$game = GameManager::getActiveGame();
		if ($game === null) {
			return;
		}
		switch ($game->getStatus()) {
			case GameStatus::VOTING:
			case GameStatus::STARTING:
				$game->removePlaying($player);
				$this->bunkers->getServer()->broadcastMessage(str_replace("{player}", $player->getName(), "§r§d{player} §r§ehas left the match early."));
				break;
			case GameStatus::INGAME:
				$this->bunkers->getServer()->broadcastMessage(str_replace("{player}", $player->getName(), "§r§d{player} §r§ehas left the match."));
				break;
		}
		if ($team->isMemberAlive($player)) $team->removeAlivePlayer($player,false);
		if($game->isSpectator($player)) $game->removeSpectator($player);
		$team->removeMember($player);
	}


	public function onMemberDeathEvent(MemberDeathEvent $event): void
	{
		$player = $event->getPlayer();
		$team = $event->getTeam()->isAlive() ? $event->getTeam() : null;
		$profile = $this->getBunkers()->getProfileManager()->getProfile($player);
		$profile->setRespawning(true);
		$killer = $event->getKiller();
		$killerTeam = $this->getBunkers()->getTeamManager()->getTeamByMember($killer);
		$reason = $event->getReason();
		$killerProfile = $this->getBunkers()->getProfileManager()->getProfile($killer);
		$killerProfile->addKill();
		switch ($reason){
			case MemberDeathEvent::PLAYER:
				$killerColor = TeamManager::$formats[$killerTeam->getName()];
				$kills = $killerProfile->getKills();
				Server::getInstance()->broadcastMessage(str_replace([
					"{player}",
					"{pColor}",
					"{kills}",
					"{killer}",
					"{kColor}",
					"{kKills}",
				], [$player->getName(),TeamManager::$formats[$team->getName()],$profile->getKills(), $killer->getName(),$killerColor,
					$kills],
					"{pColor}{player}§r§7[§f{kills}§r§7] §r§ewas slain by {kColor}{killer}§r§7[§f{kKills}§r§7]"));
				break;
			case MemberDeathEvent::FALL:
				Server::getInstance()->broadcastMessage(str_replace([
					"{player}",
					"{pColor}",
					"{kills}",
				], [$player->getName(),TeamManager::$formats[$team->getName()],$profile->getKills()],
					"{pColor}{player}§r§7[§f{kills}§r§7] §r§efell to their death."));
				break;
		}
		$team->announce("§r§c§lTeammate Death§r§7: §r§f{$player->getName()}");
		$drops = array_merge($player->getInventory()->getContents(), $player->getArmorInventory()->getContents());
		foreach ($drops as $drop) {
			$player->getWorld()->dropItem($player->getPosition(), $drop);
		}
		$player->getArmorInventory()?->clearAll();
		$player->getInventory()?->clearAll();
		$team->setDtr($team->getDTR() - 1.0);
		$team->announce("§r§eDTR: " . $team->getDTR() + 1.0 . " -> {$team->getDTR()}");
		$profile->addDeath();
		if ($team->isRaidable()) $team->removeAlivePlayer($player,true);
		else Bunkers::getInstance()->getScheduler()->scheduleRepeatingTask(new RespawnTask($player, $team), 20);
	}

	public function onTeamEntityDeath(EntityDeathEvent $event): void
	{
		$entity = $event->getEntity();
		if (Utils::isMerchant($entity) && $entity instanceof Entity) {
			$team = is_null($entity->getTeam()) ? null : $entity->getTeam();
			if ($team instanceof Team) {
				$team->startRespawn($entity->getName(), $team, $entity->getLocation());
			}
		}
	}

	public function getBunkers(): Bunkers{
		return $this->bunkers;
	}
}