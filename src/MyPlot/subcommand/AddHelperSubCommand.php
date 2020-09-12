<?php
declare(strict_types=1);
namespace MyPlot\subcommand;

use MyPlot\forms\MyPlotForm;
use MyPlot\forms\subforms\AddHelperForm;
use MyPlot\Plot;
use pocketmine\command\CommandSender;
use pocketmine\OfflinePlayer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class AddHelperSubCommand extends SubCommand
{
	/**
	 * @param CommandSender $sender
	 *
	 * @return bool
	 */
	public function canUse(CommandSender $sender) : bool {
		return ($sender instanceof Player) and $sender->hasPermission("myplot.command.addhelper");
	}

	/**
	 * @param Player $sender
	 * @param string[] $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, array $args) : bool {
		if(empty($args)) {
			return false;
		}
		$helperName = $args[0];
		$plot = $this->getPlugin()->getPlotByPosition($sender);
		if($plot === null) {
            $sender->sendMessage($this->getPlugin()->prefix . TextFormat::RED . "Du befindest dich nicht auf einem Grundstück.");
            return true;
		}
		if($plot->owner !== $sender->getName() and !$sender->hasPermission("myplot.admin.addhelper")) {
            $sender->sendMessage($this->getPlugin()->prefix . TextFormat::RED . "Du bist nicht der Besitzer dieses Grundstücks.");
            return true;
		}
		$helper = $this->getPlugin()->getServer()->getPlayer($helperName);
		if($helper === null)
			$helper = new OfflinePlayer($this->getPlugin()->getServer(), $helperName);
		if($this->getPlugin()->addPlotHelper($plot, $helper->getName())) {
			$sender->sendMessage($this->getPlugin()->prefix . TextFormat::YELLOW . $helper->getName() . TextFormat::GREEN . " wurde zum Helfer des Grundstück ernannt.");
		}else{
			$sender->sendMessage(TextFormat::RED . $this->translateString("error"));
		}
		return true;
	}

	public function getForm(?Player $player = null) : ?MyPlotForm {
		if(($plot = $this->getPlugin()->getPlotByPosition($player)) instanceof Plot)
			return new AddHelperForm($plot);
		return null;
	}
}
