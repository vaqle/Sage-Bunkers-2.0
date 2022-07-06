<?php

declare(strict_types=1);

namespace form;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use TypeError;

class Utils
{
    public static function validateObjectArray(array $array, string $class): bool
    {
        foreach($array as $key => $item) {
            if(!($item instanceof $class)) {
                throw new TypeError("Element \"$key\" is not an instance of $class");
            }
        }
        return true;
    }


	/** @var array */
	public static array $scoreboardSessions = [];

	/**
	 * $objective - Id of sb
	 * @param Player $player
	 * @param string $title
	 * @param string $objective
	 * @param string $slot
	 * @param int $order
	 */
	public static function makeScoreboard(Player $player, string $title, string $objective, string $slot = "sidebar", $order = 0)
	{
		if (isset(self::$scoreboardSessions[$player->getName()])) {
			self::removeScoreboard($player);
		}
		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = $slot;
		$pk->objectiveName = $objective;
		$pk->displayName = $title;
		$pk->criteriaName = "dummy";
		$pk->sortOrder = 0;
		$player->getNetworkSession()->sendDataPacket($pk);
		self::$scoreboardSessions[$player->getName()] = $objective;
	}

	public static function removeScoreboard(Player $player): void
	{
		$pk = new RemoveObjectivePacket();
		$pk->objectiveName = self::$scoreboardSessions[$player->getName()];
		$player->getNetworkSession()->sendDataPacket($pk);
		unset(self::$scoreboardSessions[$player->getName()]);
	}

	public static function addLine(Player $player, int $score, string $message): void
	{
		if (!isset(self::$scoreboardSessions[$player->getName()])) return;
		$objectiveName = self::$scoreboardSessions[$player->getName()];
		$entry = new ScorePacketEntry();
		$entry->objectiveName = $objectiveName;
		$entry->type = $entry::TYPE_FAKE_PLAYER;
		$entry->customName = $message;
		$entry->score = $score;
		$entry->scoreboardId = $score;
		$pk = new SetScorePacket();
		$pk->type = $pk::TYPE_CHANGE;
		$pk->entries[] = $entry;
		$player->getNetworkSession()->sendDataPacket($pk);
	}

	public static function getScoreboardSessions(): array
	{
		return self::$scoreboardSessions;
	}

}