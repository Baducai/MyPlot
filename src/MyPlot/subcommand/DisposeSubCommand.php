<?php
declare(strict_types=1);
namespace MyPlot\subcommand;

use MyPlot\forms\MyPlotForm;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class DisposeSubCommand extends SubCommand
{
	/**
	 * @param CommandSender $sender
	 *
	 * @return bool
	 */
	public function canUse(CommandSender $sender) : bool {
		return ($sender instanceof Player) and $sender->hasPermission("myplot.command.dispose");
	}

	/**
	 * @param Player $sender
	 * @param string[] $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, array $args) : bool {
		$plot = $this->getPlugin()->getPlotByPosition($sender);
		if($plot === null) {
            $sender->sendMessage($this->getPlugin()->prefix . TextFormat::RED . "Du befindest dich nicht auf einem Grundstück.");
            return true;
		}
		if($plot->owner !== $sender->getName() and !$sender->hasPermission("myplot.admin.dispose")) {
			$sender->sendMessage($this->getPlugin()->prefix . TextFormat::RED . "Du bist nicht der Besitzer dieses Grundstücks.");
			return true;
		}
		if(isset($args[0]) and $args[0] == $this->translateString("confirm")) {
			$economy = $this->getPlugin()->getEconomyProvider();
			$price = $this->getPlugin()->getLevelSettings($plot->levelName)->disposePrice;
			if($economy !== null and !$economy->reduceMoney($sender, $price)) {
				$sender->sendMessage(TextFormat::RED . $this->translateString("dispose.nomoney"));
				return true;
			}
			if($this->getPlugin()->disposePlot($plot)) {
				$sender->sendMessage($this->getPlugin()->prefix . TextFormat::GREEN . "Das Grundstück wurde freigegeben.");
			}else{
				$sender->sendMessage(TextFormat::RED . $this->translateString("error"));
			}
		}else{
			$plotId = TextFormat::GREEN . $plot . TextFormat::WHITE;
			$sender->sendMessage($this->getPlugin()->prefix . TextFormat::GREEN . "Bist du sicher, dass du das Grundstück " . TextFormat::YELLOW . $plot->X . ";" . $plot->Z . TextFormat::GREEN . " freigeben möchtest? Wenn ja, benutze /p dispose confirm.");
		}
		return true;
	}

	public function getForm(?Player $player = null) : ?MyPlotForm {
		return null;
	}
}
