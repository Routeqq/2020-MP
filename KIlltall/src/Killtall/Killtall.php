<?php

namespace Killtall;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;
use pocketmine\utils\Config;
use onebone\economyapi\EconomyAPI;

class Killtall extends PluginBase implements Listener {
   private $settingData, $settingDB;
   public function onEnable() {
      $this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
      @mkdir ( $this->getDataFolder () );
      $this->settingData = (new Config ( $this->getDataFolder () . "setting.yml", Config::YAML, [
            "뺏을돈퍼센트" => 10
      ] ));
      $this->settingDB = $this->settingData->getAll ();
   }
   public function onKill(PlayerDeathEvent $event) {
      $entity = $event->getPlayer ();
      if (! $entity instanceof Player)
         return true;
      $cause = $entity->getLastDamageCause ();
      if ($cause instanceof EntityDamageByEntityEvent || $cause instanceof EntityDamageByChildEntityEvent) {
         $damager = $cause->getDamager ();
         if (! $damager instanceof Player)
            return true;
         $ename = $entity->getName ();
         $money = EconomyAPI::getInstance ()->myMoney ( $entity );
         $ma = floor ( ( int ) $money / $this->getPercent () );
         $this->getServer ()->broadcastMessage ( "§6§l[§f 약탈§6 ]§f {$ename} 님이 {$ma} 원을 약탈 당했습니다" );
         EconomyAPI::getInstance ()->addMoney ( $damager, $ma );
         $damager->sendMessage ( "§6§l[ §f약탈 §6]§f {$ename} 에게 {$ma} 원을 약탈 했습니다" );
         EconomyAPI::getInstance ()->reduceMoney ( $entity, $ma );
         $entity->sendMessage ( "§6§l[ §f약탈l §6]§f " . $damager->getName () . " 님에게 {$ma} 원을 약탈 당했습니다" );
      }
   }
   public function getPercent() {
      $a = $this->settingDB ["뺏을돈퍼센트"];
      if (! is_numeric ( $a ))
         return false;
      if ($a > 100)
         return false;
      $b = ceil ( ( int ) 100 / $a );
      return $b;
   }
}
