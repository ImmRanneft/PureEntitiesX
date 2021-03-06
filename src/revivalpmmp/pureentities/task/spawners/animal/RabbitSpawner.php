<?php
namespace revivalpmmp\pureentities\task\spawners\animal;


use pocketmine\level\generator\biome\Biome;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\Player;
use revivalpmmp\pureentities\entity\animal\walking\Rabbit;
use revivalpmmp\pureentities\PureEntities;
use revivalpmmp\pureentities\task\spawners\BaseSpawner;

/**
 * Class RabbitSpawner
 *
 * Spawn: Rabbits naturally spawn in deserts, flower forests, taiga, mega taiga, cold taiga, ice plains, ice mountains,
 * ice spikes, and the "hills" and "M" variants of these biomes. They spawn in groups of two or three; one adult and
 * one or two babies. They have different skins that depend on the biome.
 *
 * @package revivalpmmp\pureentities\task\spawners
 */
class RabbitSpawner extends BaseSpawner {

    public function __construct() {
        parent::__construct();
    }

    public function spawn (Position $pos, Player $player) : bool {

        if ($this->spawnAllowedByProbability()) {
            $biomeId = $pos->level->getBiomeId($pos->x, $pos->z);

            // how many horses to spawn (we spawn herds)
            $herdSize = mt_rand(2, 3);

            PureEntities::logOutput($this->getClassNameShort() . ": isDay: " . $this->isDay($pos->getLevel()) .
                ", spawnAllowedByEntityCount: " . $this->spawnAllowedByRabbitCount($pos->getLevel(), $herdSize) .
                ", biomeOk: " . ($biomeId == Biome::DESERT or $biomeId == Biome::FOREST or $biomeId == Biome::TAIGA or $biomeId == Biome::PLAINS or $biomeId == Biome::BIRCH_FOREST or $biomeId == Biome::ICE_PLAINS) .
                ", playerDistanceOK: " . $this->checkPlayerDistance($player, $pos) .
                ", herdSize: $herdSize", PureEntities::DEBUG);


            if ($this->isSpawnAllowedByBlockLight($player, $pos, -1, 9) and // check block light when enabled
                $this->isDay($pos->level) and // spawn only at day
                $this->spawnAllowedByRabbitCount($pos->level, $herdSize) and // check entity count for horse, donkey and mule
                ($biomeId == Biome::DESERT or $biomeId == Biome::FOREST or $biomeId == Biome::TAIGA or $biomeId == Biome::PLAINS or $biomeId == Biome::BIRCH_FOREST or $biomeId == Biome::ICE_PLAINS) and // respect spawn biomes
                $this->checkPlayerDistance($player, $pos)) { // player distance must be ok

                // spawn 1 adult rabbit and the rest is baby rabbit
                $this->spawnEntityToLevel($pos, $this->getEntityNetworkId(), $pos->getLevel(), "Animal");
                PureEntities::logOutput($this->getClassNameShort() . ": scheduleCreatureSpawn (pos: $pos) as adult", PureEntities::NORM);

                // spawn the rest as baby (not implemented yet)
                for ($i=0; $i < ($herdSize - 1); $i++) {
                    $this->spawnEntityToLevel($pos, $this->getEntityNetworkId(), $pos->getLevel(), "Animal");
                    PureEntities::logOutput($this->getClassNameShort() . ": scheduleCreatureSpawn (pos: $pos) as baby", PureEntities::NORM);
                }
                return true;
            }

        } else {
            PureEntities::logOutput($this->getClassNameShort() . ": Spawn not allowed because of probability", PureEntities::DEBUG);
        }
        return false;
    }

    protected function getEntityNetworkId () : int {
        return Rabbit::NETWORK_ID;
    }
    protected function getEntityName () : string {
        return "Rabbit";
    }


    // ---- rabbit spawner specific -----

    /**
     * Special method because we spawn herds of rabbits (at least 2 of them)
     *
     * @param Level $level
     * @param int $herdSize
     * @return bool
     */
    protected function spawnAllowedByRabbitCount (Level $level, int $herdSize) : bool {
        if ($this->maxSpawn <= 0) {
            return false;
        }
        $count = 0;
        foreach ($level->getEntities() as $entity) { // check all entities in given level
            if ($entity->isAlive() and !$entity->closed and $entity::NETWORK_ID == Rabbit::NETWORK_ID) { // count only alive, not closed and desired entities
                $count ++;
            }
        }

        PureEntities::logOutput($this->getClassNameShort() . ": got count of  $count entities living for " . $this->getEntityName(), PureEntities::DEBUG);

        if (($count + $herdSize) < $this->maxSpawn) {
            return true;
        }
        return false;
    }

}