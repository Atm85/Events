<?php


namespace atom\eventManager;


use atom\gui\GUI;
use atom\eventManager\commands\EventCommand;
use atom\eventManager\manager\Database;
use atom\eventManager\manager\Form;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;


class Main extends PluginBase implements Listener {

    /** @var Main */
    private static $instance;

    public function onLoad(): void {
        $map = $this->getServer()->getCommandMap();
        $map->register("eventManager", new EventCommand("events", $this));
    }

    public function onEnable(): void {
        self::$instance = $this;
        $cre = $this->getConfig()->getNested("database.mysql");
        if ($cre['host'] != "" &&
            $cre['username'] != "" &&
            $cre['schema'] != ""
        ) {
            Database::connect();
            $this->getServer()->getPluginManager()->registerEvents($this, $this);
            GUI::register("event_creator", Form::event_creator());
            GUI::register("admin", Form::admin());
            GUI::register("success", Form::success());
        } else {
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }

    }

    public function onDisable(): void {
        Database::disconnect();
    }

    public static function getInstance(): Main {
        return self::$instance;
    }
}
