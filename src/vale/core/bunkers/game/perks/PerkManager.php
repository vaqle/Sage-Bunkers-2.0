<?php
namespace vale\core\bunkers\game\perks;

class PerkManager{

	public static array $perks = [];

	public static function init(): void{
	}

	public static function registerPerk(Perk $perk): void{
		self::$perks[$perk->getName()] = $perk;
	}

	public static function getPerk(string $name): ?Perk{
		return self::$perks[$name];
	}

	public static function getPerks(): array{
		return self::$perks;
	}

}