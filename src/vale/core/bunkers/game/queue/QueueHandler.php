<?php
namespace vale\core\bunkers\game\queue;
use pocketmine\player\Player;
use vale\core\bunkers\Bunkers;

class QueueHandler
{
     /** @var array $queues */
	public static array $queues = [];

	public static function add(Player $player)
	{
		self::$queues[$player->getName()] = $player;
	}

	public static function remove(Player $player)
	{
		unset(self::$queues[$player->getName()]);
	}

	public static function exists(Player $player)
	{
		return isset(self::$queues[$player->getName()]);
	}

	public static function isFull(): bool
	{
		return count(self::$queues) >= Bunkers::PLAYERS_NEEDED_TO_START;
	}

	public static function hasEnoughToStart(): bool
	{
		return count(self::$queues) >= Bunkers::PLAYERS_NEEDED_TO_START;
	}

	public static function getAll(): array
	{
		return self::$queues;
	}
}