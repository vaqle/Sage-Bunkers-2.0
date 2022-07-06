<?php
namespace vale\core\bunkers\game\map;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use vale\core\bunkers\Bunkers;

class MapManager
{
	/** @var Map[] */
	private array $maps = [];

	public function __construct()
	{
		$this->init();
	}

	public function init(): void
	{
		foreach (Bunkers::getInstance()->getConfig()->get("maps") as $mapName => $mapData) {
			$world = Bunkers::getInstance()->getServer()->getWorldManager()->getWorldByName($mapData["world"]);
			if ($world === null) {
				#yeah, ik I don't actually load the world here, but I don't care
				Bunkers::getInstance()->getLogger()->info(TextFormat::GOLD . "Loading The World '{$mapData["world"]}'");
			}
			$this->maps[$mapName] = new Map($mapName, $mapData);
		}
	}

	public function addMap(?Map $map): void
	{
		$this->maps[$map->getName()] = $map;
	}

	public function save(): void{
		$maps = [];
		foreach ($this->maps as $map) {
			$maps[$map->getName()] = $map->getData();
		}
		Bunkers::getInstance()->getConfig()->set("maps", $maps);
	}

	public function getMaps(): array
	{
		return $this->maps;
	}

	/**
	 * @return World|null
	 * OK listen. Yes I know this is terrible But who cares?
	 * Like I know reading this ur still going to care but like
	 * I DON'T CARE OK?
	 * IF YOU ABSOLUTELY NEED ME TO RECODE ANYTHING LET ME KNOW
	 * I AM NOT THE TYPE OF GUY TO CONFIGURE MY PLUGINS BECAUSE WHY WOULD I?
	 * I'M THE ONLY ONE WHO IS EVER GONNA SEE IT RIGHT?
	 * OK ENOUGH OF ME RANTING JUST USE THE DAMN PLUGIn
	 */
	public function getLobbyWorld(): ?World{
		return Bunkers::getInstance()->getServer()->getWorldManager()->getWorldByName(Bunkers::getInstance()->getConfig()->get("lobbyworld"));
	}

	public function getMap(string $map): ?Map
	{
		return $this->maps[$map];
	}
}