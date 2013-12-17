<?php

/*
__PocketMine Plugin__
name=PMFdecoder
description=Decompile PMF plugins
version=1.0
author=wies
class=PMFdecoder
apiversion=9,10
*/
		
class PMFdecoder implements Plugin{
	private $api;
	public function __construct(ServerAPI $api, $server = false){
		$this->api = $api;
	}
	
	public function init(){
		$this->api->console->register("pmfdecompile", "Decompile a pmf plugin", array($this, "command"));
	}
	
	public function command($cmd, $args, $issuer){
		if($issuer !== 'console'){
			$output = 'Must be run on the console';
			return $output;
		}
		if(!isset($args[0])){
			$output = 'Usage: /pmfdecompile <classname>';
			return $output;
		}
		$classname = strtolower(trim($args[0]));
		return $this->decompile($classname);
	}
	
	private function decompile($classname){
		$info = $this->api->plugin->getInfo($classname);
		if($info === false){
			return "The plugin class \"$classname\" does not exist";
		}
		$info = $info[0];
		$data = $info["code"];
		$data = str_replace(";", ";\r\n", $data);
		$data = str_replace("{", "{\r\n", $data);
		$data = str_replace("}", "}\r\n", $data);
		$data = "<?php

/*
__PocketMine Plugin__
name=".$info['name']."
description=
version=".$info['version']."
author=".$info['author']."
class=".$info['class']."
apiversion=".$info['apiversion']."
*/

".$data."
?>";
		file_put_contents($this->api->plugin->configPath($this).$info["name"].'.php', $data);
		return "The plugin \"$classname\" is decompiled";
	}
	
	public function __destruct(){}

}
?>