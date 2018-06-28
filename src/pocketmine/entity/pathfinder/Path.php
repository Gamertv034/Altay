<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\pathfinder;

use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class Path{

	/* @var PathPoint[] */
	protected $tiles = [];

	public function __construct(array $tiles = []){
		$this->tiles = $tiles;
	}

	public function havePath() : bool{
		return !empty($this->tiles);
	}

	public function getNextTile(Entity $entity, bool $compressPath = false) : ?PathPoint{
		if($this->havePath()){
			$next = reset($this->tiles);

			if($next->x == (int) floor($entity->x) and $next->y == (int) floor($entity->z)){
				array_shift($this->tiles);

				return $this->getNextTile($entity);
			}

			if($compressPath){
				foreach ($this->tiles as $tile){
					if($this->isClearBetweenPoints($entity->level, $entity->asVector3(), new Vector3($tile->x, floor($entity->y), $tile->y))) {
						$next = $tile;
						unset($this->tiles[array_search($tile, $this->tiles)]);
					}else{
						break;
					}
				}
			}
			return $next;
		}
		return null;
	}
	
	public function getFinalVector() : Vector3{
	    if(!$this->havePath()){
	        return new Vector3(0,0,0);
	    }
	    $end = end($this->tiles);
	    return new Vector3($end->x, $end->height, $end->y);
	}

	public function isClearBetweenPoints(Level $level, Vector3 $from, Vector3 $to) : bool{
		$entityPos = $from;
		$targetPos = $to;
		$distance = $entityPos->distance($targetPos);
		$rayPos = $entityPos;
		$direction = $targetPos->subtract($entityPos)->normalize();

		if($distance < $direction->length()){
			return true;
		}

		do{
			if ($level->getBlock($rayPos)->isSolid()){
				return false;
			}
			$rayPos = $rayPos->add($direction);
		}while($distance > $entityPos->distance($rayPos));

		return true;
	}
}