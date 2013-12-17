<?php
/*
__PocketMine Plugin__
name=UrbanDictionary
description=Just because :-)
version=0.1
author=Falkirks
class=UrbanDict
apiversion=10
*/
class UrbanDict implements Plugin{
private $api;
public function __construct(ServerAPI $api, $server = false){
$this->api = $api;
}

public function init(){

$this->api->console->register("ud", "Look up urban dictionary", array($this, "command"));
}

public function __destruct(){}

public function command($cmd, $params, $issuer, $alias, $args, $issuer){
$term = implode("%20", $params);
$resp = json_decode(file_get_contents("http://api.urbandictionary.com/v0/define?term=" . $term))->list[0]->definition;
$newtext = wordwrap($resp, 35, "<br>");
$newtext = explode("<br>", $newtext);
$issuer->sendChat("---Definition---");
foreach ($newtext as $item) {
	$issuer->sendChat($item);
}
}
}
?>