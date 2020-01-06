<?php
namespace HpShop;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\Player;

use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\entity\Entity;

use onebone\economyapi\EconomyAPI;

use pocketmine\item\Item;
use pocketmine\math\Vector3;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;

use pocketmine\utils\Config;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class HpShop extends PluginBase implements Listener {
private $chat = [];
public function onEnable() {
    @mkdir ( $this->getDataFolder () );
    
    $this->user = new Config ( $this->getDataFolder () . "user.yml", Config::YAML );
    $this->userD = $this->user->getAll ();//유저 체력
    
    
    $this->block = new Config ( $this->getDataFolder () . "block-sign.yml", Config::YAML );
    $this->bdata = $this->block->getAll (); 
    
    $this->settingData = (new Config($this->getDataFolder() . "setting.yml", Config::YAML, [
        "최대체력" => 100, "최소체력" => 20
    ]));
    $this->settingDB = $this->settingData->getAll();
    
$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
}

public function onJoin (PlayerJoinEvent $event){
    $player = $event->getPlayer();
    $name = strtolower($player->getName());
    if( ! isset( $this->userD [$name] ) ){
        $this->userD [$name] = 20;
        $this->save();
        return true;
    }
    $hp = $this->userD [$name];
    $player->setMaxHealth ($hp);
    $player->setHealth ($hp);
}

public function maJama(PlayerRespawnEvent $event){
    $player = $event->getPlayer();
    $name = strtolower($player->getName());
    if( ! isset( $this->userD [$name] ) ){
        $this->userD [$name] = 20;
        $this->save();
        return true;
    }
    $hp = $this->userD [$name];
    $player->setMaxHealth ($hp);
    $player->setHealth ($hp);
}

public function onSignChange(SignChangeEvent $event){
    if( $event->getLine(0) == "체력상점" ){
        $player = $event->getPlayer();
        $hpp = "§f[§c체력§f] ";
        if( $event->getLine(1) == "-" || $event->getLine(1) == "+" ){
            if(!$player->isOp()){
                $player->sendMessage( $hpp . "§c오피만 만들 수 있습니다.");
                $event->setCancelled();
                return true;
            }
            $x = $event->getBlock()->x;
            $y = $event->getBlock()->y;
            $z = $event->getBlock()->z;
            $world = $event->getBlock()->getLevel()->getFolderName();
            $this->bdata['체력상점'][$x.':'.$y.':'.$z.':'.$world ] = $event->getLine(1);
            $this->bdata['체력상점-돈'][$x.':'.$y.':'.$z.':'.$world ] = $event->getLine(3);
            $this->bdata['체력상점-체력'][$x.':'.$y.':'.$z.':'.$world ] = $event->getLine(2);
            $event->setLine(0, "§f[§c체력 상점§f]" );
            $event->setLine(1, "§c§l타입 §f: " . $event->getLine(1));
            $event->setLine(2, "§c§l체력 §f: §f" .  $this->bdata['체력상점-체력'][$x.':'.$y.':'.$z.':'.$world ] );
            $event->setLine(3, "§c§l가격 §f: §f" .  $this->bdata['체력상점-돈'][$x.':'.$y.':'.$z.':'.$world ]);
            
            $player->sendMessage( $hpp . " 성공적으로 만들었습니다");
            $this->save();
            return true;
        }
        else{
            $player->sendMessage( $hpp . "1 체력상점, 2 -,+, 3 -, +될 체력 4 가격");
            return true;
        }
    }
}

public function PlayerInteract(PlayerInteractEvent $event) {
    $player = $event->getPlayer ();
    $name = strtolower($player->getName());
    $havem = EconomyAPI::getInstance()->myMoney($player);
    $x = $event->getBlock()->getX ();
    $y = $event->getBlock()->getY ();
    $z = $event->getBlock()->getZ ();
    $world = $event->getBlock()->getLevel ()->getFolderName ();
    $hpp = "§f[§c체력§f] ";
    if ( isset ( $this->bdata['체력상점'][$x.':'.$y.':'.$z.':'.$world ] ) ) {
        $cas = $this->bdata['체력상점'][$x.':'.$y.':'.$z.':'.$world ];
        $money = $this->bdata['체력상점-돈'][$x.':'.$y.':'.$z.':'.$world ];
        $chp = $this->bdata['체력상점-체력'][$x.':'.$y.':'.$z.':'.$world ];
        $myhp = $this->userD [$name];
        if( $cas == "+" ){
            if( $myhp >= $this->settingDB["최대체력"] ){
                $player->sendMessage( $hpp . "더 이상 체력이 늘어날 수 없습니다");
            }else{
            if( $havem < $money ){
                $needm = $money - $havemoney;
                $player->sendMessage( $hpp . "구매하려면 {$needm}원이 더 필요합니다!");
                return true;
            }
            EconomyAPI::getInstance()->reduceMoney($player, $money);
            $this->userD [$name] = $this->userD [$name] + $chp;
            $hpa = $this->userD [$name];
            $player->sendMessage( $hpp . "체력을 구매하여 늘렸습니다!!");
            $player->setMaxHealth ($hpa);
            $player->setHealth ($hpa);
            $this->save();
            return true;
        }
    }
        if( $cas == "-" ){
            if( $myhp <= $this->settingDB["최소체력"] ){
                $player->sendMessage( $hpp . "더 이상 체력이 즐어들 수 없습니다");
            }else{
            if( $havem < $money ){
                $needm = $money - $havem;
                $player->sendMessage( $hpp . "작아지려면 {$needm}원을 더 가져오세요!");
                return true;
            }
            EconomyAPI::getInstance()->reduceMoney($player, $money);
            $this->userD [$name] = $this->userD [$name] - $chp;
            $hpa = $this->userD [$name];
            $player->sendMessage( $hpp . "체력을 구매하여 줄였습니다");
            $player->setMaxHealth ($hpa);
            $player->setHealth ($hpa);
            $this->save();
            return true;
        }
    }
}
}

public function onBreak(BlockBreakEvent $event) {
    $player = $event->getPlayer ();
    $block = $event->getBlock ();
    $x = $block->getX ();
    $y = $block->getY ();
    $z = $block->getZ ();
    $world = $block->getLevel ()->getFolderName ();
    $hpp = "§f[§c체력§f] ";
    if (isset ( $this->bdata['체력상점'][$x.':'.$y.':'.$z.':'.$world ] )) {
        if ($player->isOp ()) {
            unset ( $this->bdata['체력상점'][$x.':'.$y.':'.$z.':'.$world ] );
            unset ($this->bdata['체력상점-돈'][$x.':'.$y.':'.$z.':'.$world ]);
            $player->sendMessage ( $prefix . "체력상점 표지판 삭제했습니다" );
            $this->save();
        }
        else{
            $player->sendMessage ( $prefix . "체력상점 표지판은 관리자만 부실 수 있습니다!" );
        }
    }
}
public function save(){
    $this->user->setAll ( $this->userD );
    $this->user->save ();
    
    $this->block->setAll ( $this->bdata );
    $this->block->save ();
 
}
}