<?php
/*
__PocketMine Plugin__
apiversion=10
author=PEMapModder
name=CNR_Mapping_Plugin
description=Capture Nether Reactor Mapper plugin
version=0.0.0
class=CNR
*/
class CNR implements Plugin{
	private $api,$dir,$syncs=array(),$consoleIsAfk=false;
	public $playerWithWands=array(),$wands=array(267,0);
	public function __construct(ServerAPI $api,$server=false) {
		$this->api=$api;
	}
	public function __destruct(){
	}
	public function init() {
		$this->syncs["PEMapModder"]=true;
		$this->dir=$this->api->plugin->configPath($this);
		$this->config=new Config($this->dir."MembersProfiles.yml",CONFIG_YAML,array());
		$this->api->console->register("getPos","getPos",array($this,"getPos"));
		$this->api->ban->cmdWhitelist("getPos");
		$this->api->console->register("seePing","see a player's ping",array($this,"seePing"));
		$this->api->console->register("sync","sync quarters",array($this,"toggleSync"));
		$this->api->console->register("cafk","Toggles console's AFK status",array($this,"cafk"));
		$this->api->addHandler("player.block.touch",array($this,"blockTouch"));
#		$this->api->addHandler("player.block.place",array($this,"blockPlace"));
		$this->api->schedule(3000, array($this,"broadcastAFK"),array(),true);
		$this->api->addHandler("player.equipment.change",array($this,"equipChange"));
#		$this->api->addHandler("player.join",array($this,"playerJoin"));
#		$this->api->addHandler("tile.update",array($this,"tileUpdate"));
	}
	public function equipChange($data,$event){
		$item=$data["item"];
		$player=$data["player"]->username;
		if(!$this->api->ban->isOp($player))return;
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
		$this->api->console->run("tell @a PEMapModder is now AFK. Let's hope he comes back soon and finds that you are online.");
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
		return $player->username."'s lag: ".$lag;
	}
	public function toggleSync($c,$p,$player){
		if(sizeof($p)!==1 or !($player instanceof Player)){
			return "[ERROR] Wrong usage of /$c";
		}if ($p[0]==="on") {
			$this->syncs[$player->username]=true;
		}elseif ($p[0]==="off") {
			$this->syncs[$player->username]=false;
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
			$this->blockPlace(array("player"=>$player,"target"=>$target));
		}
		if($this->api->ban->isOp($player->username)){
			$player->sendChat("You tapped block at w:".$tLevel."(".$tX.", ".$tY.", ".$tZ.").");
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
	public function blockPlace(&$data,$event){
		$player=$data["player"];
		$level=$player->entity->level;
		if ($level->getName()==="new" and $this->syncOn($player->username)===true and in_array($player, $this->playerWithWands)) {
			$oB=$data["target"];
			$vT=new Vector3($oB->x,$oB->y,$oB->z);
			$block=$oB;
			$vX=new Vector3(255-$oB->x,$oB->y,$oB->z);
			$vXZ=new Vector3($oB->x,$oB->y,255-$oB->z);
			$vZ=new Vector3(255-$oB->x,$oB->y,255-$oB->z);
			$level->setBlockRaw($vX,$block);
			$level->setBlockRaw($vXZ,$block);
			$level->setBlockRaw($vZ,$block);
			return false;
		}
	}
	
	public function syncOn($ign){
		return $this->syncs[$ign];
	}
}
