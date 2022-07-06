<?php
namespace vale\core\bunkers\game;

use pocketmine\player\Player;

class GameManager
{
	public static array $games = [];

	public static ?Game $activeGame = null;

	public static function create(Game $game): Game
	{
		self::$games[] = $game;
		self::$activeGame = $game;
		return $game;
	}

	public static function getGame(Game $game): ?Game
	{
		return self::$games[array_search($game, self::$games, true)];
	}

	public static function getGameFromPlayer(Player $player): ?Game
	{
		foreach (self::$games as $game) {
			if ($game->hasPlayer($player)) return $game;
			else return null;
		}
		return null;
	}

	public static function getActiveGame(): ?Game
	{
		return self::$activeGame;
	}

	public static function removeGame(): void
	{
		self::$games = [];
		self::$activeGame = null;
	}
}