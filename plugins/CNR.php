<?php
/*
__PocketMine Plugin__
apiversion=10,11
author=PEMapModder
name=CNR_Mapping_Plugin
description=Capture Nether Reactor Mapper plugin
version=0.2.0
class=CNR
*/
class CNR implements Plugin{
	private $tempBundleA;
	private $api,$dir,$syncs=array(),$consoleIsAfk=false;
	public $playerWithWands=array(),$wands=array(267,0,276);
	public function __construct(ServerAPI $api,$server=false) {
		$this->api=$api;
	}
	public function __destruct(){
	}
	public function blockTouchMainHandler($data,$event){
		$this->tempBundleA=array($data,$event);
		$this->api->schedule(1,array($this,"blockTouchScheduled"),array(),false);
	}
	public function blockTouchScheduled(){
		$this->blockTouch($this->tempBundleA[0],$this->tempBundleA[1]);
	}
	public function worldCentreSyncChecker(){
		$clients=$this->api->player->getAll();
		foreach($clients as $player){
			$ign=$player->username;
			if($player->entity->level->getName()==="new" and min($player->entity->x-120, $player->entity->z-120)<18){
				$this->api->console->run("sudo $ign sync off");
			}
			if(!isset($this->syncs[$ign])){
				$player->sendChat("You forgot to do /sync.\n The plugin has just automatically\n done it for you :)");
				$this->api->console->run("sudo $ign sync on");
			}
		}
	}
	public function init() {
		$this->api->schedule(1200,array($this,"worldCentreSyncChecker",array(),false));
		$this->api->console->register("seesync","See players' sync status",array($this,"cmdHand"));
		$this->api->console->register("warp","<warp name> Warp to a warp",array($this,"warpHandler"));
		$this->syncs["PEMapModder"]=true;
		$this->dir=$this->api->plugin->configPath($this);
		$this->config=new Config($this->dir."MembersProfiles.yml",CONFIG_YAML,array());
		$this->api->console->register("getPos","getPos",array($this,"getPos"));
		$this->api->ban->cmdWhitelist("getPos");
		$this->api->console->register("qrotate", "teleports to another quarter of the world", array($this,"quartertp"));
		$this->api->console->register("switchw", "<worldID> switches world.\nIDs: 0 for lobby,\n1 for old world,\n2 for mapping world", array($this,"switchw"));
		$this->api->console->register("seePing","see a player's ping",array($this,"seePing"));
		$this->api->console->register("sync","sync quarters",array($this,"toggleSync"));
		$this->api->console->register("cafk","Toggles console's AFK status",array($this,"cafk"));
		$this->api->addHandler("player.block.touch",array($this,"blockTouchMainHandler"), 100);
#		$this->api->addHandler("player.block.place",array($this,"blockPlace"));
#		$this->api->schedule(6000, array($this,"broadcastAFK"),array(),true);
		$this->api->addHandler("player.equipment.change",array($this,"equipChange"));
		$this->api->addHandler("player.join",array($this,"playerJoin"));
#		$this->api->addHandler("tile.update",array($this,"tileUpdate"));
#		$this->api->schedule(10,array($this,"initWorlds"));
		$this->api->schedule(40,array($this,"initializeServer"),array(),false);
	}
	public function initializeServer(){
		$this->api->console->run("swl new");
		$this->api->console->run("swl lobby");
		$this->api->console->run("swl old");
		$this->api->console->run("swl prep");
		$this->api->console->run("tp @a w:new");
	}
	public function initWorlds(){
		$this->api->console->run("swl new");
		$this->api->console->run("swl old");
	}
	public function switchw($c,$a,$p){
		if(!($p instanceof Player))return "Please run this command in-game";
		$ign=$p->username;
		if(count($a)===0){
			$this->api->console->run("tp $ign w:new");return "Teleported to w:new";
		}
		switch($a[0]){
			case 0:$this->api->console->run("tp $ign w:lobby");break;
			case 1:$this->api->console->run("tp $ign w:old");break;
			case 2:$this->api->console->run("tp $ign w:new");break;
			default:return "Usage: /$c <worldID>\nIDs: 0 for lobby,\n1 for old world,\n2 for mapping world";
		}
	}
	public function quartertp($c,$a,$p){
		if(!($p instanceof Player))return "Please run this command in-game";
		$x=255-$p->entity->x;
		$y=$p->entity->y;
		$z=255-$p->entity->z;
		$this->api->console->run("tp ".$p->username." $x $y $z");
	}
	public function equipChange($data,$event){
		$item=$data["item"]->getID();
		$player=$data["player"]->username;
#		if(!$this->api->ban->isOp($player))return;
		if(in_array($player,$this->playerWithWands)){
			if(!in_array($item, $this->wands)){
				foreach($this->playerWithWands as $key=>$tplayer){
					if($tplayer===$player){
						$this->playerWithWands[$key]="null";
					}
				}
			}
			return;
		}
		if(in_array($item, $this->wands)){
			$this->playerWithWands[]=$player;
		}
	}
	public function broadcastAFK(){
		$this->api->console->run("say Remember to switch your sync mode\nwhen you are building in the world\ncenter! Switch it by\n/sync on and \n/sync off");
	}
	public function cafk($cmd,$args,$console){
		if($console instanceof Player)$console->sendChat("/$cmd is only for console to use");
		$consoleIsAfk=!$consoleIsAfk;
		if($consoleIsAfk){
			console("[CAFK] You are now in AFK status. Do /$cmd again to turn off.");return;
		}
		else console("You are no longer AFK.");return;
	}
	public function getPos($cmd,$args,$issuer){
		if(!($issuer instanceof Player)){
			return "Only usable by players.";
		}
		$issuer->sendChat("X: ".$issuer->entity->x." Y: ".$issuer->entity->y." Z: ".$issuer->entity->z);
	}
	public function seePing($c,$a,$i){
		$player=$this->api->player->get($a[0]);
		if($player===false)return "Player ".$a[0]." not found.";
		$lag=$player->getLag();
		return $player->username."'s lag: $lag\n";
	}
	public function toggleSync($c,$p,$player){
		if(sizeof($p)!==1 or !($player instanceof Player)){
			return "[ERROR] Wrong usage of /$c";
		}if ($p[0]==="on") {
			$this->syncs[$player->username]=true;
			return "Turned your sync mode on";
		}elseif ($p[0]==="off") {
			$this->syncs[$player->username]=false;
			return "Turned your sync mode off";
		}else{
			$player->sendChat("[ERROR] Wrong usage of /$c");
		}
	}
	public function blockTouch($data){
		$player=$data["player"];
		$target=$data["target"];
		$tX=$target->x;
		$tY=$target->y;
		$tZ=$target->z;
		$tLevel=$player->entity->level->getName();
		$tId=$target->getID();
		if($data["type"]=="break"){
		if($tLevel==="new" and $tLevel!="lobby"){#just to ensure things
			if($data["type"]=="break"){
				$this->editBlock($tX,$tY,$tZ,$tLevel,$tId,$player);
#				return;
			}
		}
		return;
		}
		if($tLevel==="new"){
			$this->blockChange($player,$target);
		}
		if($this->api->ban->isOp($player->username)){
#			$player->sendChat("You tapped block at w:".$tLevel."(".$tX.", ".$tY.", ".$tZ.").");
		}
		if($tId==323 or $tId==63 or $tId==68){
			# next
			if($tX==135 and $tY==57 and $tZ==125){
				$this->api->console->run("tp ".$player->username." 135 56 128");return false;
			}
			if($tX==135 and $tY==57 and $tZ==131){
				$this->api->console->run("tp ".$player->username." 135 55 134");return false;
			}
			if($tX==135 and $tY==56 and $tZ==134){
				$this->api->console->run("tp ".$player->username." 135 63 134");return false;
			}
			if($tX==135 and $tY==64 and $tZ==134){
				$this->api->console->run("tp ".$player->username." 135 64 131");return false;
			}
			if($tX==135 and $tY==65 and $tZ==131){
				$this->api->console->run("tp ".$player->username." 135 63 128");return false;
			}
			if($tX==135 and $tY==64 and $tZ==128){
				$this->api->console->run("tp ".$player->username." 135 63 125");return false;
			}
			if($tX==135 and $tY==64 and $tZ==125){
				$this->api->console->run("tp ".$player->username." 135 71 133");return false;
			}
			if($tX==135 and $tY==72 and $tZ==134){
				$this->api->console->run("tp ".$player->username." 135 71 130");return false;
			}
			if($tX==136 and $tY==56 and $tZ==125){
				$this->api->console->run("tp ".$player->username." 125.5 55 128.5");return false;
			}
			if(($tX==127 and $tY==56 and $tZ==127) or (($tX==128 or $tX==129)) and $tZ==127){
				$this->api->console->run("tp ".$player->username." 135 56 125");return false;
			}
			# previous
			if($tX==135 and $tY==57 and $tZ==124){
				$player->sendChat(FORMAT_RED."N/A");return false;
			}
		}
	}
	public function playerJoin($player){
		$this->syncs[$player->username]=false;
	}
	public function editBlock($x,$y,$z,$level,$id=0,$player){
		if($this->syncOn($player->username)===true){
			$level=$player->entity->level;
			$vT=new Vector3($x,$y,$z);
			$vX=new Vector3(255-$x,$y,$z);
			$vXZ=new Vector3(255-$x,$y,255-$z);
			$vZ=new Vector3($x,$y,255-$z);
			$air=BlockAPI::get(0);
			$level->setBlockRaw($vX,$air);
			$level->setBlockRaw($vXZ,$air);
			$level->setBlockRaw($vZ,$air);
		}
	}
	public function blockChange(Player $player, Block $target){
		$x=$target->x;
		$y=$target->y;
		$z=$target->z;
		$level=$player->entity->level;
		$this->blockPlace($player, $target);
		$this->blockPlace($player, $level->getBlockRaw(new Vector3($x+1,$y,$z)));
		$this->blockPlace($player, $level->getBlockRaw(new Vector3($x-1,$y,$z)));
		$this->blockPlace($player, $level->getBlockRaw(new Vector3($x,$y+1,$z)));
		$this->blockPlace($player, $level->getBlockRaw(new Vector3($x,$y-1,$z)));
		$this->blockPlace($player, $level->getBlockRaw(new Vector3($x,$y,$z+1)));
		$this->blockPlace($player, $level->getBlockRaw(new Vector3($x,$y,$z-1)));
	}
	public function blockPlace(Player $player, $target){
#		$player=$data["player"];
		$level=$player->entity->level;
		if ($level->getName()==="new" and $this->syncOn($player->username)===true /*and in_array($player, $this->playerWithWands)*/) {
#			$oB=$data["target"];
			$oB=$target;
			$block=$target;
			$vT=new Vector3($oB->x,$oB->y,$oB->z);
			$vX=new Vector3(255-$oB->x,$oB->y,$oB->z);
			$vXZ=new Vector3($oB->x,$oB->y,255-$oB->z);
			$vZ=new Vector3(255-$oB->x,$oB->y,255-$oB->z);
			$level->setBlockRaw($vX,$block);
			$level->setBlockRaw($vXZ,$block);
			$level->setBlockRaw($vZ,$block);
			return false;
		}
	}
/*	#public function playerJoin($data,$event){
		if($data->getGamemode()===1){
			$this->api->console->run("gamemode 3 ".$data->username);
			$data->sendChat("You are automatically changed to gamemode 3");
		}
	}
*/
	public function syncOn($ign){
		return $this->syncs[$ign];
	}
	public function warpHandler($c,$a,$player){
		if(!($player instanceof Player))return "Please run this command in-game.";
		$p=$this->api->player->get($player);
		$warp=implode(" ",$a);
		switch($warp){
			case "team creeper prep":
				if ($player->getGamemode()===1) {
					$this->api->console->run("gamemode 3 $p");
				}
				$this->api->console->run("swl prep");
				$this->api->console->run("tp $p w:prep");
				$this->api->console->run("tp $p 50 37 50");
				break;
			case "map creeper spawn":
				$this->api->console->run("swl new");
				$this->api->console->run("tp $p w:new");
				$this->api->console->run("tp $p 4.5 50 4.5");
				$this->api->console->run("gamemode 3 $p");
			}
	}
	public function cmdHand($c,$a,$player){
		$ign=$player->username;
		$sentence="/$c ".implode(" ",$a);
		switch ($c) {
			case "seesync":
				$result="";
				foreach ($this->syncs as $player => $isSyncOn) {
					if($isSyncOn){
						$sentence.="$player sync on\n";
					}
					else $sentence.="$player sync off\n";
				}
				return $result;
			break;
		}
	}
}
