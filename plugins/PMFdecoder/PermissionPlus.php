<?php

/*
__PocketMine Plugin__
name=PermissionPlus
description=
version=1.0.9
author=Omattyao
class=permissionplus
apiversion=7,8,9,10,11
*/

define("PERMISSION_PLUS_VER", "");
 class PermissionPlus implements Plugin{
 private $api, $path, $config;
 const VERSION ="1.0.9";
public function __construct(ServerAPI $api, $server =false){
 $this->api =$api;
 }
public function init(){
 $this->createConfig();
 $this->formatConfig();
 $this->api->event('player.spawn', array($this, 'handle'));
 $this->api->addHandler('console.command', array($this, 'handle'), 15);
 $this->api->addHandler('get.player.permission', array($this, 'returnPermission'), 1);
 $this->api->console->register('ppplayer', '[Permission+] Manages player permission.', array($this, 'commandH'));
 $this->api->console->register('ppcommand', '[Permission+] Manages command permission.', array($this, 'commandH'));
 $this->api->console->register('ppconfig', '[Permission+] PermissionPlus preferences.', array($this, 'commandH'));
 $this->api->console->register('ppsub', '[Permission+] PermissionPlus preferences.', array($this, 'commandH'));
 $this->api->console->alias("ppplyr", "ppplayer");
 $this->api->console->alias("ppcmd", "ppcommand");
 $this->api->console->alias("ppcfg", "ppconfig");
 }
public function __destruct(){
 }
public function handle($data, $event) {
switch ($event) {
case "console.command": if (!($data['issuer'] instanceof Player))break;
 if ($this->config["cmd-whitelist"] === true) {
$this->cmdWhitelist($data["cmd"]);
 }
$user =$data['issuer']->username;
 $cmdCheck =$this->checkPermission($user, $data['cmd'], array_shift($data['parameters']), $this->config["notice"]);
 if ($data['alias'] !== false) {
$aliasCheck =$this->checkPermission($user, $data['alias'], false, false);
 }
else {
$aliasCheck =true;
 }
if (!$cmdCheck || !$aliasCheck)return false;
 break;
 case "player.spawn": $user =$data->username;
 if ($this->getUserPermission($user) === false) {
$this->config['player'][$user] ='GUEST';
 $this->writeConfig();
 }
if ($this->config["autoop"] === true) {
$this->giveOP($user);
 }
break;
 }
}
public function commandH($cmd, $params, $issuer, $alias) {
$output ="";
 if ($issuer instanceof Player) {
$username =$issuer->username;
 }
switch ($cmd) {
case "ppplayer": $player =array_shift($params);
 $permission =array_shift($params);
 if (empty($player) || empty($permission)) {
if ($issuer === "console") {
$this->showPPermissionsList();
 }
$msg =$this->permissionUsage("p");
 $output .= "Usage: /ppplayer <player> $msg
";
 break;
 }
if (!$this->hackChecker())break;
 $this->setPPermission($player, $permission, $output);
 $this->writeConfig();
 break;
 case "ppcommand": $command =array_shift($params);
 if (empty($command)) {
if ($issuer === "console") {
$this->showCPermissionsList();
 }
$msg =$this->permissionUsage("c");
 $output .= "Usage: /ppcommand <command> $msg
";
 break;
 }
if (!$this->hackChecker())break;
 $this->setCPermission($command, $params, $output);
 $this->writeConfig();
 break;
 case "ppsub": $cmd =array_shift($params);
 $subcmd =array_shift($params);
 if (empty($cmd) || empty($subcmd)) {
if (empty($cmd)) {
if ($issuer === "console") {
$this->showSPermissionsList();
 }
}
$msg =$this->permissionUsage("c");
 $output .= "Usage: /ppsub <cmd> <subcmd> $msg
";
 break;
 }
$this->setSPermission($cmd, $subcmd, $params, $output);
 $this->writeConfig();
 break;
 case "ppconfig": $config =str_replace("-", "", array_shift($params));
 switch ($config) {
case "notice": $bool =array_shift($params);
 if (!$this->castBool($bool)) {
$output .= "Usage: /ppconfig notice <on | off>
";
 break;
 }
$this->config["notice"] =$bool;
 if ($bool) {
$output .= "[Permission+] Truned on to the notify function.
";
 }
else {
$output .= "[Permission+] Truned off to the notify function.
";
 }
break;
 case "autoop": if (!$this->hackChecker())break;
 $bool =array_shift($params);
 if (!$this->castBool($bool)) {
$output .= "Usage: /ppconfig autoop <on | off>
";
 break;
 }
$this->config["autoop"] =$bool;
 if ($bool) {
$this->giveOPtoEveryone();
 $output .= "[Permission+] Truned on to the auto op function.
";
 }
else {
$output .= "[Permission+] Truned off to the auto op function.
";
 }
break;
 case "cmdwhitelist": case "cmdw": if (!$this->hackChecker())break;
 $bool =array_shift($params);
 if (!$this->castBool($bool)) {
$output .= "Usage: /ppconfig cmd-whitelist <on | off>
";
 break;
 }
$this->config["cmd-whitelist"] =$bool;
 if ($bool) {
$output .= "[Permission+] Truned on to the cmd-whitelist function.
";
 }
else {
$output .= "[Permission+] Truned off to the cmd-whitelist function.
";
 $output .= "\x1b[33;
1m[Permission+] You have to restart PocketMine-MP to apply the setting!\x1b[0m
";
 }
break;
 case "add": $name =array_shift($params);
 if (empty($name) || !$this->isAlnum($name)) {
$output .= "Usage: /ppconfig add <rank name>
";
 break;
 }
if ($this->addPermission($name)) {
$output .= "[Permission+] Successful!
";
 }
else {
$output .= "[Permission+] Failed to add!
";
 }
break;
 case "rm": case "remove": $name =array_shift($params);
 if (empty($name) || !$this->isAlnum($name)) {
$output .= "Usage: /ppconfig remove <rank name>
";
 break;
 }
if ($this->removePermission($name)) {
$output .= "[Permission+] Successful!
";
 }
else {
$output .= "[Permission+] Failed to remove!
";
 }
break;
 default: $output .= "Usage: /ppconfig notice <on | off>
";
 $output .= "Usage: /ppconfig autoop <on | off>
";
 $output .= "Usage: /ppconfig add <rank name>
";
 $output .= "Usage: /ppconfig remove <rank name>
";
 }
$this->writeConfig();
 break;
 }
return $output;
 }
private function setPPermission($player, $permission, &$output) {
if (!$this->castPermission($permission)) {
$msg =$this->permissionUsage("p");
 $output .= "Usage: /ppplayer <player> $msg
";
 return;
 }
$this->config['player'][$player] =$permission;
 $output .= "[Permission+] Gived ".$permission." to ".$player.".
";
 $this->api->chat->sendTo(false, "[PermissionPlus] Your permission has been changed into ".$permission." !", $player);
 }
private function setCPermission($cmd, $permissions, &$output) {
$msg ="";
 $return =array_fill_keys($this->getPermissions(), false);
 if (!empty($permissions)) {
foreach ($permissions as $permission) {
$value =$permission;
 if (!$this->castPermission($permission)) {
$output .= "[Permission+] Invalid value: \"$value\"
";
 continue;
 }
$msg .= $permission." ";
 $return[$permission] =true;
 }
}
foreach ($this->getPermissions() as $permission) {
$this->config["command"][$permission][$cmd] =$return[$permission];
 }
if (empty($msg)) {
$output .= "[Permission+] \"/".$cmd."\" was disabled.
";
 }
else {
$output .= "[Permission+] Assigned ".$msg."to \"/".$cmd."\".
";
 }
}
private function setSPermission($cmd, $sub, $permissions, &$output) {
$return =array_fill_keys($this->getPermissions(), false);
 if (!empty($permissions)) {
foreach ($permissions as $permission) {
$value =$permission;
 if (!$this->castPermission($permission)) {
$output .= "[Permission+] Invalid value: \"$value\"
";
 continue;
 }
$msg .= $permission." ";
 $return[$permission] =true;
 }
}
foreach ($this->getPermissions() as $permission) {
$this->config["subcmd"][$permission][$cmd][$sub] =$return[$permission];
 }
if (empty($msg)) {
$output .= "[Permission+] \"/".$cmd." ".$sub."\" was disabled.
";
 }
else {
$output .= "[Permission+] Assigned ".$msg."to \"/".$cmd." ".$sub."\".
";
 }
}
public function showPPermissionsList() {
$output ="
";
 foreach ($this->config["player"] as $name => $permission) {
$online =$this->api->player->get($name);
 if ($online) {
$online ="\x1b[33;
1mONLINE";
 }
else {
$online ="\x1b[34mOFFLINE";
 }
if ($this->config["permission"][$permission]) {
$output .= "[".$online."\x1b[0m][\x1b[32m".$permission."\x1b[0m]:  $name
";
 }
else {
$output .= "[".$online."\x1b[0m][\x1b[36m".$permission."\x1b[0m]:  $name
";
 }
}
console($output);
 }
public function showCPermissionsList() {
$output ="
";
 $permission =array();
 $clist =$this->config['command'];
 foreach ($this->getPermissions() as $prm) {
$pname =substr($prm, 0, 5);
 foreach ($clist[$prm] as $command => $enable) {
if ($enable) {
if($this->config["permission"][$prm]) {
$permission[$prm][$command] ="[\x1b[32m".$pname."\x1b[0m]";
 }
else {
$space =str_repeat(" ", 5 -strlen($prm));
 $permission[$prm][$command] ="[\x1b[36m".$pname."\x1b[0m]" .$space;
 }
}
else {
$permission[$prm][$command] ="       ";
 }
}
}
foreach ($this->getCommands() as $command) {
foreach ($this->getPermissions() as $prm) {
$output .= $permission[$prm][$command];
 }
$output .= ":  /".$command."
";
 }
console($output);
 }
public function showSPermissionsList() {
$output ="
";
 $permission =array();
 $clist =$this->config['subcmd'];
 foreach ($this->getPermissions() as $prm) {
$pname =substr($prm, 0, 5);
 foreach ($clist[$prm] as $command => $subcmds) {
foreach ($subcmds as $sub => $enable) {
if ($enable) {
if($this->config["permission"][$prm]) {
$permission[$prm][$command."_".$sub] ="[\x1b[32m".$pname."\x1b[0m]";
 }
else {
$space =str_repeat(" ", 5 -strlen($prm));
 $permission[$prm][$command."_".$sub] ="[\x1b[36m".$pname."\x1b[0m]" .$space;
 }
}
else {
$permission[$prm][$command."_".$sub] ="       ";
 }
}
}
}
foreach ($this->config["subcmd"]["ADMIN"] as $command => $subcmds) {
foreach (array_keys($subcmds) as $sub) {
foreach ($this->getPermissions() as $prm) {
$output .= $permission[$prm][$command."_".$sub];
 }
$output .= ":  /".$command." ".$sub."
";
 }
}
console($output);
 }
public function checkPermission($player, $cmd, $sub, $notice) {
$permission =$this->getUserPermission($player);
 if ($notice && !isset($this->config['command']['ADMIN'][$cmd])) {
console("[Permission+] NOTICE: \"/".$cmd."\" permission is not setted!");
 console("[Permission+] Usage: /ppcommand ".$cmd." (g) (t) (a)");
 }
if (!empty($sub)) {
if (isset($this->config['subcmd'][$permission][$cmd][$sub]) && !$this->config['subcmd'][$permission][$cmd][$sub]) {
return false;
 }
}
if (isset($this->config['command'][$permission][$cmd]) && !$this->config['command'][$permission][$cmd]) {
return false;
 }
else {
return true;
 }
}
public function getUserPermission($user) {
if ($user instanceof Player) {
$user =$user->username;
 }
if (!isset($this->config['player'][$user]))return false;
 return $this->config['player'][$user];
 }
public function getCommands() {
$cmds =array_keys($this->config['command']['ADMIN']);
 return $cmds;
 }
public function getPermissions() {
$prms =array_keys($this->config["permission"]);
 return $prms;
 }
public function giveOP($user) {
$this->api->ban->commandHandler("op", array($user), "console", false);
 }
public function giveOPtoEveryone() {
$players =$this->api->player->getAll();
 if (count($players) != 0) {
foreach ($players as $player) {
$user =$player->username;
 $this->giveOP($user);
 }
}
}
public function cmdWhitelist($cmd) {
$this->api->ban->cmdWhitelist($cmd);
 }
public function addPermission($permission) {
$permission =strtoupper($permission);
 $permissions =$this->getPermissions();
 if (!in_array($permission, array_merge(array("g", "t", "a"), $permissions))) {
$this->config["permission"][$permission] =false;
 $this->config["command"][$permission] =array_fill_keys($this->getCommands(), false);
 $this->config["subcmd"][$permission] =array();
 foreach ($this->config["subcmd"]["ADMIN"] as $cmd => $subcmds) {
$this->config["subcmd"][$permission][$cmd] =array();
 foreach (array_keys($subcmds) as $sub) {
$this->config["subcmd"][$permission][$cmd][$sub] =false;
 }
}
return true;
 }
return false;
 }
public function removePermission($permission) {
$permission =strtoupper($permission);
 if (isset($this->config["permission"][$permission]) && !$this->config["permission"][$permission]) {
unset($this->config["permission"][$permission]);
 unset($this->config["command"][$permission]);
 unset($this->config["subcmd"][$permission]);
 return true;
 }
return false;
 }
public function createConfig() {
$default =array( "notice" => true, "autoop" => false, "cmd-whitelist" => true, "player" => array(), "permission" => array( "GUEST" => true, "TRUST" => true, "ADMIN" => true ),"subcmd" => array( "ADMIN" => array(), "TRUST" => array(), "GUEST" => array(), ),"command" => array("ADMIN" => array( 'ban' => true, 'banip' => true, 'defaultgamemode' => true, 'deop' => true, 'difficulty' => true, 'gamemode' => true, 'give' => true, 'help' => true, 'kick' => true, 'kill' => true, 'list' => true, 'me' => true, 'op' => true, 'ping' => true, 'save-all' => true, 'save-off' => true, 'save-on' => true, 'say' =>true, 'seed' => true, 'spawn' => true, 'spawnpoint' => true, 'status' => true, 'stop' => true, 'sudo' => true, 'tell' => true, 'time' => true, 'tp' => true, 'whitelist' => true, 'ppplayer' => true, 'ppcommand' => true, 'ppconfig' => true, 'noexplosion' => true, ),"TRUST" => array( 'ban' => false, 'banip' => false, 'defaultgamemode' => false, 'deop' => true, 'difficulty' => false, 'gamemode' => false, 'give' => true, 'help' => true, 'kick' => true, 'kill' => false, 'list' => true, 'me' => true, 'op' => true, 'ping' => true, 'save-all' => false, 'save-off' => false, 'save-on' => false, 'say' => true, 'seed' => true, 'spawn' => true, 'spawnpoint' => true, 'status' => true, 'stop' => false, 'sudo' => false, 'tell' => true, 'time' => true, 'tp' => true, 'whitelist' => false, 'ppplayer' => false, 'ppcommand' => false, 'ppconfig' => false, 'noexplosion' => false, ),"GUEST" => array( 'ban' => false, 'banip' => false, 'defaultgamemode' => false, 'deop' => false, 'difficulty' => false, 'gamemode' => false, 'give' => false, 'help' => false, 'kick' => false, 'kill' => false, 'list' => true, 'me' => false, 'op' => false, 'ping' => false, 'save-all' => false, 'save-off' => false, 'save-on' => false, 'say' => false, 'seed' => false, 'spawn' => false, 'spawnpoint' => false, 'status' => false, 'stop' => false, 'sudo' => false, 'tell' => false, 'time' => false, 'tp' => false, 'whitelist' => false, 'ppplayer' => false, 'ppcommand' => false, 'ppconfig' => false, 'noexplosion' => false, )));
$this->path =$this->api->plugin->createConfig($this, $default);
 $this->config =$this->api->plugin->readYAML($this->path."config.yml");
 $permissions =array_keys($this->config["permission"], false);
 if (count($permissions) !== 0) {
$originals ="";
 $cmds =$this->getCommands();
 foreach ($permissions as $permission) {
foreach ($cmds as $cmd) {
$originals["command"][$permission][$cmd] =false;
 }
}
$this->path =$this->api->plugin->createConfig($this, $originals);
 $this->config =$this->api->plugin->readYAML($this->path."config.yml");
 }
}
public function formatConfig() {
if (!isset($this->config["version"]) || $this->config["version"] != self::VERSION) {
if (!isset($this->config["version"])) {
$version ="1.0.8";
 }
else {
$version =$this->config["version"];
 }
switch ($version) {
case "1.0.8": foreach ($this->getPermissions() as $prm) {
if (!isset($this->config["subcmd"][$prm])) {
$this->config["subcmd"][$prm] =array();
 foreach ($this->config["subcmd"]["ADMIN"] as $cmd => $subcmds) {
$this->config["subcmd"][$prm][$cmd] =array();
 foreach (array_keys($subcmds) as $sub) {
$this->config["subcmd"][$prm][$cmd][$sub] =false;
 }
}
}
}
break;
 }
}
$this->config["version"] =self::VERSION;
 $this->writeConfig();
 }
public function writeConfig() {
$this->api->plugin->writeYAML($this->path."config.yml", $this->config);
 }
public function returnPermission($data, $event) {
return $this->getUserPermission($data);
 }
private function castPermission(&$permission) {
$permission =strtoupper($permission);
 switch ($permission) {
case "A": case "ADMIN": $permission ="ADMIN";
 break;
 case "T": case "TRUST": $permission ="TRUST";
 break;
 case "G": case "GUEST": $permission ="GUEST";
 break;
 default: if (in_array($permission, $this->getPermissions())) {
return true;
 }
$permission =false;
 return false;
 }
return true;
 }
private function castBool(&$bool) {
$bool =strtoupper($bool);
 switch ($bool) {
case "TRUE": case "ON": case "1": $bool =true;
 break;
 case "FALSE": case "OFF": case "0": $bool =false;
 break;
 default: return false;
 }
return true;
 }
private function isAlnum($text) {
if (preg_match("/^[a-zA-Z0-9]+$/",$text)) {
return true;
 }
else {
return false;
 }
}
private function permissionUsage($type) {
switch ($type) {
case "p": $output ="<";
 $border= "";
 foreach ($this->getPermissions() as $prm) {
$prm =strtolower($prm);
 $output .= $border.$prm;
 $border =" | ";
 }
$output .= ">";
 break;
 case "c": $output ="";
 foreach ($this->getPermissions() as $prm) {
$prm =strtolower($prm);
 $output .= "(".$prm.")";
 }
break;
 default: return false;
 }
return $output;
 }
private function hackChecker() {
$information =debug_backtrace();
 $plugins =$this->api->plugin->getList();
 foreach ($information as $info) {
if (!empty($info["class"]) && !in_array($info["class"], array("ConsoleAPI", "PluginAPI", "PermissionPlus", "PocketMinecraftServer"))) {
$name =strtolower($info["class"]);
 foreach ($plugins as $plugin) {
if ($plugin["class"] == $name) {
console("\x1b[31;
1m[Permission+] That plugin tried to run PermissionPlus's command!!\x1b[0m: \"\x1b[33m".$name."\x1b[0m\"");
 return false;
 }
}
}
}
return true;
 }
}

?>