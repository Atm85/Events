<?php


namespace atom\eventManager\commands;


use atom\gui\GUI;
use atom\eventManager\manager\Form;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class EventCommand extends PluginCommand {

    public function __construct(string $name, Plugin $plugin) {
        parent::__construct($name, $plugin);
        $this->setDescription("View upcoming events!");
        $this->setAliases(["ev"]);
    }

    public function execute(CommandSender $sender, string $command, array $args) {
        if ($sender instanceof Player) {
            if (!isset($args[0])) {
                Form::menu($sender);
            } else if ($sender->isOp() && $args[0] === "manage") {
                GUI::send($sender, "admin");
            }
        }
    }

}
