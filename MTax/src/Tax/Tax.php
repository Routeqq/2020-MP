<?php
namespace Tax;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use onebone\economyapi\EconomyAPI;
use pocketmine\Player;

class Tax extends PluginBase implements Listener {

    public function onEnable() {
        @mkdir ( $this->getDataFolder () );
        $this->settingData = (new Config($this->getDataFolder() . "setting.yml", Config::YAML, [
           "세금율" => 0.03,






           
           "시간" => 10,
           "접두사" => " §b[§f세금§b]§f"
         ]));
        $this->settingDB = $this->settingData->getAll();

        $this->getServer ()->getPluginManager ()->registerEvents ( $this, $this);

        $this->getScheduler()->scheduleRepeatingTask(new Taxx($this), 20*$this->settingDB["시간"]);

    }

    public function taxxx(Player $player){
      $name = $player->getName();
      $havemoney = EconomyAPI::getInstance()->myMoney($player);
      $money = $havemoney*$this->settingDB["세금율"];
      $givemoney = $money*0.01;
      EconomyAPI::getInstance()->reduceMoney($player, $givemoney);
      $player->sendMessage($this->settingDB["접두사"]." 세금을 가진돈의 {$this->settingDB["세금율"]}%로 {$givemoney}원을 냈습니다");
      $this->save();
    }
    public function save(){
        $this->settingData->setAll ( $this->settingDB );
        $this->settingData->save ();
    }


}

class Taxx extends Task {
  private $plugin;
  public function __construct(Tax $plugin) {
  $this->plugin = $plugin;

  }


  public function onRun($currentTick) {
    foreach ( $this->plugin->getServer ()->getOnlinePlayers () as $player ) {
        $this->plugin->taxxx($player);
    }
  }

}


?>
