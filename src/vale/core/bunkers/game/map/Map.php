<?php
namespace vale\core\bunkers\game\map;

use form\FormIcon;
use pocketmine\math\Vector3;
use pocketmine\world\Position;
use pocketmine\world\World;
use vale\core\bunkers\Bunkers;

class Map
{

	/** @var World|null $world */
	public ?World $world;

	public function __construct(
		public string $name,
		public array  $data
	)
	{
		Bunkers::getInstance()->getServer()->getWorldManager()->loadWorld($this->data["world"]);
		$this->world = Bunkers::getInstance()->getServer()->getWorldManager()->getWorldByName($this->data["world"]);
	}

	public function getSpawn(string $team): array
	{
		foreach ($this->data["teams"] as $index => $datum) {
			if ($datum["name"] === $team) {
				return $datum;
			}
		}
		return [];
	}

	public function getCenter(string $team): Vector3
	{
		return new Vector3((int)$this->getSpawn($team)["x"], (int)$this->getSpawn($team)["y"], (int)$this->getSpawn($team)["z"]);
	}

	public function getAttributes(string $team, string $attribute): ?Vector3
	{
		$position = explode(":", $this->getSpawn($team)[$attribute]);
		return new Vector3((int)$position[0], (int)$position[1], (int)$position[2]);
	}

	public function getTeamClaimData(string $team): array
	{
		foreach ($this->data["teams"] as $index => $datum) {
			if ($datum["name"] === $team) {
				return $datum["claim"];
			}
		}
		return [];
	}

	public function getKothData(): array
	{
		foreach ($this->data["koth"] as $index => $datum) {
			return [
				"name" => $datum["name"],
				"firstpos" => $datum["firstpos"],
				"secondpos" => $datum["secondpos"],
				"claimpos1" => $datum["claimpos1"],
				"claimpos2" => $datum["claimpos2"],
			];
		}
		return [];
	}

	public function convertStringToPosition(string $string): Position
	{
		$array = explode(":", $string);
		return new Position((int)$array[0], (int)$array[1], (int)$array[2],$this->getWorld());
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getData(): array
	{
		return $this->data;
	}


	public function getWorld(): ?World{
		return $this->world;
	}
}