<?php
namespace vale\core\bunkers\team\claim;

use pocketmine\world\Position;
use vale\core\bunkers\team\structure\Team;

class Claim
{
	private ?Team $team;

	public string $name;

	public Position $first;

	public Position $second;

	public function __construct(string $name, ?Team $team, Position $first, Position $second)
	{
		$this->name = $name;
		$this->team = $team;
		$this->first = $first;
		$this->second = $second;
	}

	public function isInRegion(Position $pos): bool
	{
		$firtPos = $this->first;
		$secondPos = $this->second;
		$maxX = max($firtPos->getX(), $secondPos->getX());
		$minX = min($firtPos->getX(), $secondPos->getX());
		$maxZ = max($firtPos->getZ(), $secondPos->getZ());
		$minZ = min($firtPos->getZ(), $secondPos->getZ());
		$maxY = max($firtPos->getY(), $secondPos->getY());
		$minY = min($firtPos->getY(), $secondPos->getY());
		//check if the $pos is inside the cube //TODO fix y values
		return $pos->getX() >= $minX && $pos->getX() <= $maxX && $pos->getZ() >= $minZ && $pos->getZ() <= $maxZ && $pos->getY() >= $minY && $pos->getY() <= $maxY;
	}

	public function getTeam(): ?Team
	{
		return $this->team;
	}
}