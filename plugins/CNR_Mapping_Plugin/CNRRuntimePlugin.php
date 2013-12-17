<?php
/*
__PocketMine Plugin__
apiversion=10
author=PEMapModder
name=CNR Runtime Plugin
version=alpha 0.0.0
class=CNRRP
*/
/*
Copyright (C) 2013 PEMapModder
Definition of terms:
public server: an MCPE PocketMine-MP server that everyone with access to the Internet can access
modifications: a clone of this
LICENSE: PLUGIN BY PEMapModder. You may view the code and learn the PHP syntax and skills from it, but you may not use it or any of its modifications on public servers without permission from PEMapModder on forums.pocketmine.net or MCPE_modder_for_maps on www.minecraftforum.net via private messages, or from pemapmodder1970@gmail.com via emails. You are welcome to commit improvements of this by pushing pull requests on your forks of this repository ( https://github.com/pemapmodder/CNR.git ) 
*/
class CNRRP implements Plugin{
	private $api;
	public function __construct(Server API $a,$s){$this->api=$a;}
	public function __destruct(){}
	//memo: 50, 9, 50
	public function init(){}
}