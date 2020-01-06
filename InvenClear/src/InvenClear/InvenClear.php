<?php

namespace InvenClear;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;


class InvenClear extends PluginBase implements Listener {
public function onEnable() {
$this->getServer()->getPluginManager()->registerEvents ( $this, $this);
$this->Majang = new Config ( $this->getDataFolder() . "Majang.yml" , Config::YAML,
["인벤초기화" => [ ]]
);

$this->majang = $this->Majang->getAll();
$this->getServer()->getPluginManager()->registerEvents ( $this, $this);
}

public function onCommand(CommandSender $sender, Command $command, $label, array $args): bool{
$command = $command->getName();
$name = $sender->getName();
$tag = " §c[§f인벤§c]§f ";
if ( $command == "인벤") {
if (! $sender->isOp ()) {
$sender->sendMessage ( $tag. "권한이 없습니다." );
return true;
}
if ( ! isset ($args[0])) {
$sender->sendMessage ($tag . " /인벤 초기화 -자신이 인벤 초기화" );
return true;
}
switch ($args[0]) {
case "초기화" :
$sender->getInventory()->clearAll();
$sender->sendMessage($tag. "자신의 인벤토리를 초기화 했습니다!");
break;
}
}
return true;
}
}
