<?php
namespace vale\core\bunkers\team\claim;

use pocketmine\world\Position;
use vale\core\bunkers\team\structure\Team;

class ClaimManager{

	public static array $claims = [];

	public static function registerClaim(Team $team, Claim $claim){
		self::$claims[$team->getName()] = $claim;
	}

	public static function getClaimByTeam(Team $team): ?Claim{
		if(isset(self::$claims[$team->getName()])){
			return self::$claims[$team->getName()];
		}
		return null;
	}

	public function deleteClaim(Claim $claim){
		unset(self::$claims[$claim->getTeam()->getName()]);
	}

	/**
	 * @param Position $pos
	 * @return Claim|null
	 */
	public static function getClaimAtPosition(Position $pos): ?Claim{
		foreach(self::$claims as $claim){
			if($claim->isInRegion($pos)){
				return $claim;
			}
		}
		return null;
	}
}