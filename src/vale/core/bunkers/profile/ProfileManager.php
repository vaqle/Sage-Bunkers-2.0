<?php
namespace vale\core\bunkers\profile;

use pocketmine\player\Player;
use vale\core\bunkers\profile\structure\Profile;

class ProfileManager{

	private array $profiles = [];

	public function addProfile(Player  $player): void{
		$this->profiles[$player->getName()] = new Profile($player);
		$this->profiles[$player->getName()]->init();
	}

	public function deleteProfile(Player $player): void{
		unset($this->profiles[$player->getName()]);
	}

	public function getProfileFrom(Player $player): ?Profile{
		return $this->profiles[array_search($player, $this->profiles)] ?? null;
	}

	public function getProfile(Player $player): ?Profile{
		return $this->profiles[$player->getName()] ?? null;
	}

	public function getProfileByName(string $name): ?Profile{
		return $this->profiles[$name] ?? null;
	}

	public function getProfiles(): array{
		return $this->profiles;
	}
}