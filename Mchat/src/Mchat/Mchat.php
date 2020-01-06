<?php

namespace Mchat;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class Mchat extends PluginBase implements Listener {
	private $config;
	private $chat;
	public function onEnable() {
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		@mkdir ( $this->getDataFolder () );
		$this->config = new Config ( $this->getDataFolder () . "Config.yml", Config::YAML, [
				"second" => 2,
				"prefix" => "§b [§f채팅§b] §f"
		] );
	}
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args):bool {
		$command = $command->getName ();
		if ($command == "채팅") {
			if (! $sender->isOp ()) {
				$sender->sendMessage ( $this->config->get ( "prefix" ) . "권한이 없습니다." );
				return true;
			}
			if (! isset ( $args [0] )) {
				$sender->sendMessage ( $this->config->get ( "prefix" ) . "/채팅 시간설정 (초)" );
			} elseif ($args [0] == "시간설정") {
				if (! isset ( $args [1] ) || ! is_numeric ( $args [1] )) {
					$sender->sendMessage ( $this->config->get ( "prefix" ) . "딜레이는 숫자로 입력해야 합니다." );
					return true;
				}
				$this->config->set ( "second", $args [1] );
				$this->save ();
				$sender->sendMessage ( $this->config->get ( "prefix" ) . "채팅 시간을 {$args[1]}초로 설정했습니다." );
			} else {
				$sender->sendMessage ( $this->config->get ( "prefix" ) . "/채팅 시간설정  (초)" );
			}
			return true;
		}
	}
	public function onChat(PlayerChatEvent $event) {
		$player = $event->getPlayer ();
		$name = $player->getName ();
		if (! isset ( $this->chat [$name] )) {
		    $this->chat [$name] = $this->makeTimestamp ();
			return true;
		}
		if ($this->makeTimestamp () - $this->chat [$name] < $this->config->get ( "second" )) {
			$event->setCancelled ( true );
			$player->sendMessage ( $this->config->get ( "prefix" ) . "채팅은 ". $this->config->get ( "second" ). "초 후에 가능합니다 !" );
		} else {
		    $this->chat [$name] = $this->makeTimestamp ();
		}
	}
	public function makeTimestamp() {
		$date = date ( "Y-m-d H:i:s" );
		$yy = substr ( $date, 0, 4 );
		$mm = substr ( $date, 5, 2 );
		$dd = substr ( $date, 8, 2 );
		$hh = substr ( $date, 11, 2 );
		$ii = substr ( $date, 14, 2 );
		$ss = substr ( $date, 17, 2 );
		return mktime ( $hh, $ii, $ss, $mm, $dd, $yy );
	}
	public function save() {
		$this->config->setAll ( $this->config->getAll () );
		$this->config->save ();
	}
	public function onDisable() {
		$this->save ();
	}
}
