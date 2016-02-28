<?php

namespace yuu528;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\scheduler\PluginTask;

class AIChat extends PluginBase implements Listener
{
	public function onLoad()
	{
		$this->getServer()->getLogger()->info("[AIChat] ロード");
	}

	public function onEnable()
	{
		//message, registerevent
		$this->getServer()->getLogger()->info("[AIChat] 有効");
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		//config
		if(!file_exists($this->getDataFolder())){//configを入れるフォルダが有るかチェック
			mkdir($this->getDataFolder(), 0744, true);//なければフォルダを作成
		}
		$this->name = new Config($this->getDataFolder() . "name.yml", Config::YAML);
		$this->ai = new Config($this->getDataFolder() . "ai.yml", Config::YAML);


		if(!$this->name->exists("name")){
			$this->name->set("name", "鯖たん");//値と名前を設定
			$this->name->save();//設定を保存
		}

		if(!$this->ai->exists("おはよう") and !$this->ai->exists("こん") and !$this->ai->exists("あらされて")){
			$this->ai->set("おはよう", "おはようございますー！");//値と名前を設定
			$this->ai->set("こん", "こんにちはー！");
			$this->ai->set("あらされて", "あらされてますか！？どうしましょう！！！");
			$this->ai->save();//設定を保存
		}
	}

	public function onDisable()
	{
		$this->getServer()->getLogger()->info("[AIChat] 無効");
	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
	{
		switch (strtolower($command->getName())) {
			case 'aichat':
				if(!isset($args[0])) return false;
				$ainame = $this->name->get("name");
				$message = implode(" ", $args);
				$this->getServer()->broadcastMessage("[{$ainame}] {$message}");
				return true;
				break;
			//case "aichat" closed

			case 'aiadd':
				if(!isset($args[0])) return false;
				if(!isset($args[1])) return false;
				if($this->ai->exists($args[0])){
					$sender->sendMessage("[AIChat] 既に設定されたトリガー");
					return true;
					break;
				}
				$this->ai->set($args[0], $args[1]);
				$this->ai->save();
				$sender->sendMessage("[AIChat] 保存完了");
				$sender->sendMessage(" 言葉 - {$args[0]}");
				$sender->sendMessage(" 返答 - {$args[1]}");

				return true;
				break;
			//case "aiadd" closed

			case 'aidel':
				if(!isset($args[0])) return false;
				if(!$this->ai->exists($args[0])){
					$sender->sendMessage("[AIChat] 存在しません");
					return true;
					break;
				}
				$olddata = $this->ai->get($args[0]);
				$this->ai->remove($args[0]);
				$this->ai->save();
				$sender->sendMessage("[AIChat] 削除完了");
				$sender->sendMessage(" 言葉 - {$args[0]}");
				$sender->sendMessage(" 返答 - {$olddata}");
				return true;
				break;
			//case "aidel" closed

			case 'ailist':
				$allkey = $this->ai->getAll(true);
				$allkeyall = implode("\n", $allkey);

				$sender->sendMessage($allkeyall);
				return true;
				break;
			//case "ailist" closed

			case 'ainame':
			if (!isset($args[0])) {
				$sender->sendMessage("[AIChat] AIName: " . $this->ai->get("name"));
			}

			$this->name->remove("name");
			$this->name->set("name", $args[0]);
			$this->name->save();
			$sender->sendMessage("[AIChat] 変更完了");
			$sender->sendMessage(" AIName - {$args[0]}");
			return true;
			break;
		}
	}

	public function onChat(PlayerChatEvent $e){
		$pmessage = $e->getMessage();
		$player = $e->getPlayer();
		$pname = $player->getName();
		$ainame = $this->name->get("name");
		$alldata = $this->ai->getAll();


		foreach($alldata as $key => $data){
			if (preg_match("/$key/", $pmessage)) {
				$e->setCancelled(true);
				if(preg_match("/%p/", $data)){
					$aichat = str_replace("%p", $pname, $data);
				}else{
					$aichat = $data;
				}

				$this->getServer()->broadcastMessage("<{$pname}> {$pmessage}");
				$this->getServer()->broadcastMessage("[{$ainame}] " . $aichat);
				break;
			}
		}
	}
}
