<?php
namespace vale\core\bunkers\team\structure;

use pocketmine\block\Block;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\GameMode;
use pocketmine\player\Player;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\world\particle\BlockBreakParticle;
use vale\core\bunkers\Bunkers;
use vale\core\bunkers\entity\types\CombatEntity;
use vale\core\bunkers\entity\types\SellItems;
use vale\core\bunkers\game\GameManager;
use vale\core\bunkers\game\map\Map;
use vale\core\bunkers\hotbar\HotBar;
use vale\core\bunkers\profile\structure\ProfileStatus;
use vale\core\bunkers\team\claim\Claim;
use vale\core\bunkers\team\claim\ClaimManager;
use vale\core\bunkers\util\TeamUtil;
use vale\core\bunkers\util\Utils;
use WolfDen133\WFT\Texts\FloatingText;
use WolfDen133\WFT\WFT;

class Team
{
	private string $name;
	private Vector3 $combatShop;
	private Vector3 $sellShop;
	private Vector3 $buildShop;
	private Vector3 $abilityShop;
	private Vector3 $enchantShop;
	private array $respawn = [];
	private float $dtr = 6.1;
	private array $alivePlayers = [];
	private array $members = [];
	private array $blocks = [];
	private Vector3 $center;
	public bool $alive = false;
	public TaskHandler $handler;

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	public function load(Map $map): void
	{
		$this->alive = true;
		$this->center = $map->getCenter($this->getName());
		$this->combatShop = $map->getAttributes($this->getName(), "combatshop");
		$this->sellShop = $map->getAttributes($this->getName(), "sellshop");
		$world = $map->getWorld();
		$combatShopEntity = new CombatEntity(new Location($this->getCombatShop()->getX() + 0.5, $this->getCombatShop()->getY() + 1, $this->getCombatShop()->getZ() + 0.5, $world, 177, -2), $this);
		$sellShopEntity = new SellItems(new Location($this->getSellShop()->getX() + 0.5, $this->getSellShop()->getY() + 1, $this->getSellShop()->getZ() + 0.5, $world, 177, -2), $this);
		//you need to create 2 more entitys ability shop and enchant shop
		//you also need positions you can get them from the atrributes of the map
		//that should be everything apart from finishing shop
		$combatShopEntity->spawnToAll();
		$sellShopEntity->spawnToAll();
		$claimData = $map->getTeamClaimData($this->getName());
		ClaimManager::registerClaim($this, new Claim($this->getName(), $this, $map->convertStringToPosition($claimData["firstpos"]), $map->convertStringToPosition($claimData["secondpos"])));
		$this->handler = Bunkers::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function () {
			static $handlers = [];
			if (!$this->isAlive()) {
				throw new CancelTaskException();
			}
			if (count($this->getAlivePlayers()) <= 0) {
				$this->setAlive(false);
			}
			if (empty($handlers)) {
				$handlers["moneyRegen"] = time();
			}
			foreach ($this->blocks as $index => $data) {
				$time = $data[1];
				$block = $data[0];
				if (time() - $time > 15) {
					unset($this->blocks[$index]);
					/** @var Block|$block */
					$block->getPosition()->getWorld()->addParticle($block->getPosition(), new BlockBreakParticle($block));
					$block->getPosition()->getWorld()->setBlock($block->getPosition()->asVector3(), $block);
				}
			}
			foreach ($this->respawn as $e => $datum) {
				$entity = $datum[0];
				$team = $datum[1];
				$time = $datum[2];
				$position = $datum[3];
				$text = $datum[4] ?? null;
				if (!isset($datum[4])) {
					$this->respawn[$e][4] = new FloatingText($position, "ok", "§r§e$entity\n §r§7Respawns in " . gmdate("i:s", time() - $time));
					$text = $this->respawn[$e][4];
					Utils::spawnTo($text);
				}
				if ($text !== null) {
					$text->setText("§r§e$entity\n §r§7Respawns in " . gmdate("i:s", 30 - (time() - $time)));
					WFT::getAPI()->respawnToAll($text);
				}
				if (30 - (time() - $time) <= 0) {
					unset($this->respawn[$e]);
					Utils::closeTo($text);
					$e = Utils::getNewEntityFromName($entity, $position, $team);
					$e->spawnToAll();
				}
			}
			foreach ($handlers as $handler => $time) {
				if (time() - $time >= TeamUtil::getTimeFor($handler)) {
					$handlers[$handler] = time();
					TeamUtil::handle($this, $handler);
				}
			}
		}), 20);
		$members = $this->members;
		array_map(function (Player $member) use ($world) {
			assert($this->getCenter() !== null);
			$member->teleport(new Location( $this->getCenter()->getX(), $this->getCenter()->getY(), $this->getCenter()->getZ(), $world, 0, 0));
			$profile = Bunkers::getInstance()->getProfileManager()->getProfile($member);
			$profile->setStatus(ProfileStatus::PLAYING);
			$profile->giveKit();
			//TODO perks()
			#ex $perk = $profile->getPerk();
			//$ev = new PerkActivationEvent($member, $perk)->call();

			$this->alivePlayers[$member->getName()] = $member;
		}, $members);
	}

	public function reset(): void
	{
		foreach ($this->getMembers() as $member) {
			$profile = Bunkers::getInstance()->getProfileManager()->getProfile($member);
			$profile->reset();
		}
		$this->alive = false;
		$this->members = [];
		$this->alivePlayers = [];
		$this->dtr = 6.1;
		$this->respawn = [];
		$this->blocks = [];
		$this->handler->cancel();
	}

	public function getAlivePlayers(): array
	{
		return $this->alivePlayers;
	}

	public function removeAlivePlayer(Player $player, bool $value): void
	{
		if($value){
			GameManager::getActiveGame()->addSpectator($player);
		}
		unset($this->alivePlayers[$player->getName()]);
	}

	public function getHandler(): TaskHandler
	{
		return $this->handler;
	}

	public function isRaidable(): bool
	{
		return $this->dtr < 0;
	}

	public function isMemberAlive(Player $player): bool
	{
		return in_array($player, $this->alivePlayers);
	}

	public function hasMember(Player $player): bool
	{
		return in_array($player, $this->members);
	}

	public function addMember(Player $player): void
	{
		if (!$this->hasMember($player)) {
			$this->members[] = $player;
		}
	}

	public function setAlive(bool $alive): void
	{
		$this->alive = $alive;
	}

	public function removeMember(Player $player): void
	{
		if ($this->hasMember($player)) {
			$this->members = array_diff($this->members, [$player]);
		}
	}

	public function startRespawn(string $name, Team $team, Location $location)
	{
		$this->respawn[] = [$name, $team, time(), $location];
	}

	public function addToBlocksQueue(Block $block): void
	{
		$this->blocks[] = [$block, time()];
	}

	public function getCombatShop(): Vector3
	{
		return $this->combatShop;
	}

	public function getEnchantShop(): Vector3
	{
		return $this->enchantShop;
	}

	public function getSellShop(): Vector3
	{
		return $this->sellShop;
	}

	public function getBuildShop(): Vector3
	{
		return $this->buildShop;
	}

	public function getAbilityShop(): Vector3
	{
		return $this->abilityShop;
	}

	public function getCenter(): Vector3
	{
		return $this->center;
	}

	public function isAlive(): bool
	{
		return $this->alive;
	}

	public function getMembers(): array
	{
		return $this->members;
	}

	public function setDtr(float $dtr): void
	{
		$this->dtr = $dtr;
	}

	public function getDTR(): float
	{
		return $this->dtr;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function announce(string $string): void
	{
		foreach ($this->members as $member) {
			if ($member instanceof Player) {
				$member->sendMessage($string);
			}
		}
	}

	public function getMember(Player $player): ?Player
	{
		return $this->members[$player->getName()] ?? null;
	}

	public function respawnPlayer(Player $player): void{
		$player->teleport($this->getCenter());
		$player->getHungerManager()->setFood(20);
		$player->setHealth(20);
	}
}
