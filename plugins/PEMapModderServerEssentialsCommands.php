<?php
/*
__PocketMine Plugin__
class=PEMapModderEssentialsCmds
apiversion=10,11
version=0
name=PEMapModder Server Essentials Commands
author=PEMapModder
*/
class PEMapModderEssentialsCmds implements Plugin{
	public function __construct(ServerAPI $a,$s=0){}
	public function __destruct(){}
	public function init(){
		$this->s=ServerAPI::request();
		$api=$this->s->api;
		$this->ct=$api->chat;
		$this->p=$api->player;
		$b=ServerAPI::request()->api->ban;
		$c=ServerAPI::request()->api->console;
		$c->register("getping","<ign> get ping of a player",array($this,"getPing"));
		$b->cmdWhitelist("getping");
		$c->register("seearmor","<ign> see if a player is sleeping",array($this,"seeArmor"));
		$b->cmdWhitelist("seearmor");
		$c->register("seegm","<ign> see the gamemode of a player",array($this,"seeGm"));
		$b->cmdWhitelist("seegm");
		$c->register("getpos","<ign> get the position of a player",array($this,"getPos"));
		$b->cmdWhitelist("getpos");
		$c->register("pw","",array($this,"pingWarn"));
		$this->s->addHandler("player.spawn",array($this,"entered"));
	}
	public function pingWarn($c,$a,$p){
		foreach($this->s->api->player->getAll() as $p){
			if(isset($p->ping)){
				console("$p ping is $p->ping} ms.");
				if($p->ping>1000){
					$p->sendChat("Your ping is too high!");
					$p->sendChat("Having a ping of {$p->ping} ms is a poor connection!");
				}
			}
		}
	}
	public function entered($d,$e){console("$d entered");}
	public function getPing($c,$a,$p){
		if(!isset($a[0])){
			if(!($p instanceof Player))
				return "Usage: /getping <ign>";
		}
		else $p=ServerAPI::request()->api->player->get($a[0]);
		if($p===false)return "Player not found";
		return "{$p}'s ping: {$p->getLag()} pakcet loss: {$p->getPacketLoss()} bandwidth: {$p->getBandwidth()}";
	}
	public function seeArmor($c,$a,$p){
		if(!isset($a[0])){
			if(!($p instanceof Player))
				return "Usage: /seearmor <ign>";
		}
		else $p=ServerAPI::request()->api->player->get($a[0]);
		if($p===false)return "Player not found";
		return "Helmet:     ".substr($p->getArmor(0),5)."\nChestplate: ".substr($p->getArmor(1),5)."\nLeggings:   ".substr($p->getArmor(2),5)."\nBoots:      ".substr($p->getArmor(3),5);
	}
	public function seeGm($c,$a,$p){
		if(!isset($a[0])){
			if(!($p instanceof Player))
				return "Usage: /seegm <ign>";
		}
		else $p=ServerAPI::request()->api->player->get($a[0]);
		if($p===false)return "Player not found";
		return "$p is in {$p->getGamemode()} mode.";
	}
	public function getPos($c,$a,$p){
		if(!isset($a[0])){
			if(!($p instanceof Player))
				return "Usage: /seearmor <ign>";
			$i=$p;
		}
		else $i=ServerAPI::request()->api->player->get($a[0]);
		if($i===false)return "Player not found";
		$p=$i->entity;
		return "$i is in world \"".$p->level->getName()."\" at (X,Y,Z) ({$p->x},{$p->y},{$p->z}).";
	}
}
