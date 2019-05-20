<?php


namespace atom\plugin;


use atom\gui\GUI;
use atom\plugin\commands\EventCommand;
use atom\plugin\manager\Database;
use atom\plugin\manager\Form;
use DateTime;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\plugin\PluginBase;


class Main extends PluginBase implements Listener {

    /** @var Main */
    private static $instance;

    public function onLoad(): void {
        $map = $this->getServer()->getCommandMap();
        $map->register("events", new EventCommand("events", $this));
    }

    public function onEnable(): void {
        self::$instance = $this;
        Database::connect();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        GUI::register("event_creator", Form::event_creator());
        GUI::register("admin", Form::admin());
        GUI::register("success", Form::success());
    }

    public function onDisable(): void {
        Database::disconnect();
    }

    public static function getInstance(): Main {
        return self::$instance;
    }

    public function onSneak(PlayerToggleSneakEvent $event): void {
		$player = $event->getPlayer();
    }
}
