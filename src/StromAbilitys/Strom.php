<?php

namespace StromAbilitys;

use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\command\CommandSender;
use pocketmine\level\sound\AnvilBreakSound;
use pocketmine\utils\TextFormat as TE;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

class Strom extends PluginBase implements Listener {
  
  public $cooldownEffectsDisabler = [];
  public $cooldownGrapp = [];
  public $cooldownPrePearl = [];
  
  public function onEnable(){
    $this->getLogger()->info("Activated Correctly");
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }
  
  public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
    switch ($command){
      case "abilitys":
        if($sender instanceof Player){
          if($sender->hasPermission("abilitys.cmd.use")){
            $this->getEffectsDisabler($sender);
            $this->getPrePearl($sender);
            $this->getGrapplingHook($sender);
          }
        }
        break;
    }
    return true;
  }
  
  public function getEffectsDisabler(Player $player){
    $item = Item::get(341, 0, 1);
    $item->setCustomName("§r§l§cEffectsDisabler");
    $item->setLore(["§7Erase The Effects On Your Enemy"]);
    $player->getInventory()->addItem($item);
    $player->sendMessage("§aYou got the ability §eEffecDisabler \n §dCooldown: 2m");
  }
  
  public function getPrePearl(Player $player){
    $item = Item::get(381, 0, 1);
    $item->setCustomName("§r§l§bPrePearl");
    $item->setLore(["§7Return To The Position Where You Use This Item"]);
    $player->getInventory()->addItem($item);
    $player->sendMessage("§aYou got the ability §ePrePearl \n §dCooldown: 1:30m");
  }
  
  public function getGrapplingHook(Player $player){
    $item = Item::get(346, 0, 1);
    $item->setCustomName("§r§l§6GrapplingHook");
    $item->setLore(["§7Push yourself with this rod \n §dCooldown: 1m"]);
    $player->getInventory()->addItem($item);
    $player->sendMessage("§aYou got the ability §eGrapplingHook");
  }
  
  
  
  public function onPlayerInteractEvent(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		$item = $event->getItem();
		if($item->getCustomName() == "§r§l§6GrapplingHook"){
		  if(!isset($this->cooldownGrapp[$player->getName()])){
		    $this->cooldownGrapp[$player->getName()] = time() + 60;
		    $player->setMotion($player->getDirectionVector()->add(0, 0.2, 0)->multiply(3));
		  }else if(time() < $this->cooldownGrapp[$player->getName()]){
		    $reaming = $this->cooldownGrapp[$player->getName()] - time();
		    $player->sendMessage("§cCooldown GrapplingHook: ".$reaming."s");
		  }else{
		  unset($this->cooldownGrapp[$player->getName()]);
		}
		}
		if($item->getCustomName() == "§r§l§bPrePearl"){
		  if(!isset($this->cooldownPrePearl[$player->getName()])){
		  $this->cooldownPrePearl[$player->getName()] = time() + 90;
		  $position = $player->getPosition();
		  $player->sendMessage("§aYou used the PrePearl in 10 seconds you will return to the place where you activated it");
		  $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($position, $player): void {
		    if($player->isOnline())
		    $player->teleport($position);
		    $player->sendMessage("§eYou returned to the place where you activated the PrePearl");
		  }), 10 * 20);
		  }else if(time() < $this->cooldownPrePearl[$player->getName()]){
		    $reaming = $this->cooldownPrePearl[$player->getName()] - time();
		    $player->sendMessage("§cCooldown PrePearl: ".$reaming."s");
		  }else{
		  unset($this->cooldownPrePearl[$player->getName()]);
		}
		}
  }
  
  public function onEntityDamageByEntityEvent(EntityDamageByEntityEvent $event){
    $damager = $event->getDamager();
		$entity = $event->getEntity();
		if($damager->getInventory()->getItemInHand()->getCustomName() === "§r§l§cEffectsDisabler"){
		  if(!isset($this->cooldownEffectsDisabler[$damager->getName()])){
		  $entity->sendMessage("§aYour Effects Were Removed By".$damager->getName());
		  $damager->sendMessage("§aYou erase the effects of ".$entity->getName());
		  $entity->removeAllEffects();
		  $this->cooldownEffectsDisabler[$damager->getName()] = time() + 120;
		  }else if(time() < $this->cooldownEffectsDisabler[$damager->getName()]){
		    $reaming = $this->cooldownEffectsDisabler[$damager->getName()] - time();
		    $damager->sendMessage("§cCooldown EffectsDisabler: ".$reaming."s");
		}else{
		  unset($this->cooldownEffectsDisabler[$damager->getName()]);
		}
  }
}
  
}