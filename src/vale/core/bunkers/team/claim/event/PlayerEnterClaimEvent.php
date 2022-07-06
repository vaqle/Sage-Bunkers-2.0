<?php
namespace vale\core\bunkers\team\claim\event;

use pocketmine\entity\Location;
use pocketmine\event\Cancellable;
use pocketmine\event\player\PlayerEvent;
use pocketmine\player\Player;
use vale\core\bunkers\team\structure\Team;

class PlayerEnterClaimEvent extends PlayerEvent implements Cancellable
{
	public Location $from;

	public Location $to;

	public ?Team $fromTeam = null;

	public ?Team $toTeam = null;

	public function __construct(Player $player, Location $from, Location $to, ?Team $fromTeam, ?Team $toTeam)
	{
		$this->player = $player;
		$this->from = $from;
		$this->to = $to;
		$this->fromTeam = $fromTeam;
		$this->toTeam = $toTeam;
	}

	public function getFrom(): Location
	{
		return $this->from;
	}

	public function getTo(): Location
	{
		return $this->to;
	}

	public function getFromTeam(): ?Team
	{
		return $this->fromTeam;
	}

	public function getToTeam(): ?Team
	{
		return $this->toTeam;
	}

	public function isCancelled(): bool
	{
		return false;
	}
}