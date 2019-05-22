<?php


namespace atom\eventManager\manager;


use atom\eventManager\Main;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Database {

    /** @var DataConnector */
    private static $database;

    public static function connect(): void {
        $plugin = Main::getInstance();
        self::$database = libasynql::create($plugin, $plugin->getConfig()->get("database"), [
            "mysql" => "mysql.sql"
        ]);

        self::getDatabase()->executeGeneric("manager.init.main");
        self::getDatabase()->executeGeneric("manager.init.admins");
        self::getDatabase()->waitAll();
    }

    public static function disconnect(): void {
        if (isset(self::$database)) self::getDatabase()->close();
    }

    public static function getDatabase(): DataConnector {
        return self::$database;
    }

}
