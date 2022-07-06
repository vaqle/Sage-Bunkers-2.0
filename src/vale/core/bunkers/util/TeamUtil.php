<?php
namespace vale\core\bunkers\util;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AdventureSettingsPacket;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\team\structure\Team;

class TeamUtil
{
	const DTR_REGEN = 300;
	const MONEY_REGEN = 5;

	public static function getTimeFor(string $handler): int
	{
		return match ($handler) {
			"dtrRegen" => self::DTR_REGEN,
			"moneyRegen" => self::MONEY_REGEN,
			default => 0,
		};
	}

	public static function equalsTeam(Team $team1, Team $team2): bool{
		return $team1->getName() === $team2->getName();
	}

	public static function setImmutable(Player $player): void{
		$pk = new AdventureSettingsPacket();
		$pk->targetActorUniqueId = $player->getId();
		$pk->setFlag(AdventureSettingsPacket::MINE, false);
		$pk->setFlag(AdventureSettingsPacket::NO_CLIP, true);
		$pk->setFlag(AdventureSettingsPacket::BUILD, false);
		$pk->setFlag(AdventureSettingsPacket::DOORS_AND_SWITCHES, false);
		$pk->setFlag(AdventureSettingsPacket::FLYING, false);
		$pk->setFlag(AdventureSettingsPacket::WORLD_IMMUTABLE,true);
		$pk->setFlag(AdventureSettingsPacket::OPEN_CONTAINERS, false);
		$pk->setFlag(AdventureSettingsPacket::ATTACK_PLAYERS, false);
		$pk->setFlag(AdventureSettingsPacket::OPEN_CONTAINERS, false);
		$player->getNetworkSession()->sendDataPacket($pk);
	}

	/**
	 * @param Team $team
	 * @param string $handler
	 * @return void
	 */
	public static function handle(Team $team, string $handler): void
	{
		switch ($handler) {
			case "moneyRegen":
				foreach ($team->getMembers() as $member) {
					$profile = Bunkers::getInstance()->getProfileManager()->getProfile($member);
					$profile->addMoney(3);
				}
				break;
		}
	}
}