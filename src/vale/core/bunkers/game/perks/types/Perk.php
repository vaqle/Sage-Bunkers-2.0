<?php
namespace vale\core\bunkers\game\perks;

use pocketmine\item\Item;

class Perk{

	public const BREAK = 0;
	public const ATTACK = 1;
	public const GIVE = 2;

	public function __construct(
		public string $name,
		public $callback,
		public int $type = self::BREAK,
	){
	}


	public function getType(): int
	{
		return $this->type;
	}

	public function getCallBack(){
		return $this->callback;
	}

	public function getName(){
		return $this->name;
	}

	public function getItem(): ?Item{

	}
}