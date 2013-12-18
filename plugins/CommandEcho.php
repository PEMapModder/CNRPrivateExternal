<?php

/*
__Pocketmine Plugin__
name=CommandEcho
version=1.0.0
author=ZacHack
class=cmdecho
apiversion=10
*/

class cmdecho implements Plugin{
		private $api;
		public function __construct(ServerAPI $api, $server = false){
				$this->api = $api;
		}
		
		public function init(){
				$this->api->addHandler("console.command", array($this, "handle"));
		}
		
		public function __destruct(){
		}
		
		public function handle($data, $event){
				$cmd = $data['cmd'];
				$user = $data['issuer'];
				switch($event){
						case "console.command":
								if($data['issuer'] instanceof Player){
										console(FORMAT_AQUA."[CommandEcho] ".$user." has used /".$cmd." ");
										if($cmd==="cafk" or $cmd==="whitelist" or $cmd==="ban" or $cmd==="banip"){
											if(false){$this->api->console->run("kick $user using cmd $cmd");return false;}
										}
										continue;
								}
				}
		}
}