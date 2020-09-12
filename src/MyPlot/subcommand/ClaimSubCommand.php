<?php
declare(strict_types=1);
namespace MyPlot\subcommand;

use MyPlot\forms\MyPlotForm;
use MyPlot\forms\subforms\ClaimForm;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ClaimSubCommand extends SubCommand
{
	/**
	 * @param CommandSender $sender
	 *
	 * @return bool
	 */
	public function canUse(CommandSender $sender) : bool {
		return ($sender instanceof Player) and $sender->hasPermission("myplot.command.claim");
	}

	/**
	 * @param Player $sender
	 * @param string[] $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, array $args) : bool {
		$name = "";
		if(isset($args[0])) {
			$name = $args[0];
		}
		$plot = $this->getPlugin()->getPlotByPosition($sender);
		if($plot === null) {
			$sender->sendMessage($this->getPlugin()->prefix . TextFormat::RED . "Du befindest dich nicht auf einem Grundstück.");
			return true;
		}
		if($plot->owner != "") {
			if($plot->owner === $sender->getName()) {
				$sender->sendMessage($this->getPlugin()->prefix . TextFormat::RED . "Das Grundstück wurde bereits von dir beansprucht.");
			}else{
				$sender->sendMessage($this->getPlugin()->prefix . TextFormat::RED . "Das Grundstück wurde bereits von " . TextFormat::YELLOW . $plot->owner . TextFormat::RED . " beansprucht.");
			}
			return true;
		}
		$maxPlots = $this->getPlugin()->getMaxPlotsOfPlayer($sender);
		$plotsOfPlayer = 0;
		foreach($this->getPlugin()->getPlotLevels() as $level => $settings) {
			$level = $this->getPlugin()->getServer()->getLevelByName((string)$level);
			if(!$level->isClosed()) {
				$plotsOfPlayer += count($this->getPlugin()->getPlotsOfPlayer($sender->getName(), $level->getFolderName()));
			}
		}
		if($plotsOfPlayer >= $maxPlots) {
			$sender->sendMessage($this->getPlugin()->prefix . TextFormat::RED . "Du kannst keine weiteren Grundstücke besitzen.");
			return true;
		}
		$plotLevel = $this->getPlugin()->getLevelSettings($plot->levelName);
		$economy = $this->getPlugin()->getEconomyProvider();
		if($economy !== null and !$economy->reduceMoney($sender, $plotLevel->claimPrice)) {
			$sender->sendMessage(TextFormat::RED . $this->translateString("claim.nomoney"));
			return true;
		}
		if($this->getPlugin()->claimPlot($plot, $sender->getName(), $name)) {
			$sender->sendMessage($this->getPlugin()->prefix . TextFormat::GREEN . "Das Grundstück gehört nun dir.");
		}else{
			$sender->sendMessage(TextFormat::RED . $this->translateString("error"));
		}
		return true;
	}

	public function getForm(?Player $player = null) : ?MyPlotForm {
		return new ClaimForm($player);
	}
}
