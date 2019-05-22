<?php


namespace atom\eventManager\manager;



use atom\gui\type\CustomGui;
use atom\gui\type\ModalGui;
use atom\gui\type\SimpleGui;
use atom\eventManager\Main;
use DateTime;
use pocketmine\form\Form as GUI;
use pocketmine\Player;
use pocketmine\utils\TextFormat as color;

class Form {

    public const MONTHS = [
        "Select one",
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December"
    ];

    public static function menu(Player $player)/*: GUI*/ {
        Database::getDatabase()->executeSelect("manager.select.events", [],
        function (array $rows) use($player) : void {
            Database::getDatabase()->executeSelect("manager.select.admin", [
                "uuid" => $player->getUniqueId()->toString()
            ],
            function (array $data) use ($player, $rows): void {
                $menu = new SimpleGui();
                $menu->setTitle(color::BOLD.color::DARK_AQUA."Upcoming Events!");
                if (empty($rows)) {
                    $menu->setContent("No Events Were Found!");
					$button = color::BOLD.color::DARK_RED."Create Event";
                    foreach ($data as $index) {
                        if ($index['COUNT(*)'] !== 0) $menu->addButton($button);
                    }
                } else {
                    foreach ($rows as $row) {
                        $day = $row['day'];
                        if (strlen($day) === 1) $day = "0".$day;
                        $month = date('m', strtotime($row['month']));
                        $year = substr($row['year'], -2);
                        $rawdate = [$day, $month, $year];
                        $datetime = implode("/", $rawdate);
                        $today = new DateTime();
                        $today->setTime(00, 00, 00, 00);
                        $eventDate = DateTime::createFromFormat('d/m/y', $datetime);
                        $eventDate->setTime(00, 00, 00, 00);
                        if ($eventDate == $today) {
                            $text = color::BOLD.color::AQUA.$row['month'].' - '.$row['day'].' - '.$row['year'];
                            $menu->addButton($text);
                        }
                        if ($eventDate > $today) {
                            $text = color::BOLD.color::DARK_GREEN.$row['month'].' - '.$row['day'].' - '.$row['year'];
                            $menu->addButton($text);
                        }
                    }
                    $button = color::BOLD.color::DARK_RED."Create Event";
                    foreach ($data as $index) {
                        if ($index['COUNT(*)'] !== 0) $menu->addButton($button);
                    }
                }
                $menu->setAction(function (Player $player, $data) use($rows): void {
                    $button = color::BOLD.color::DARK_RED."Create Event";
                    if ($data === $button) \atom\gui\GUI::send($player, "event_creator");
                    foreach ($rows as $row) {
                        $text = color::BOLD.color::DARK_GREEN.$row['month'].' - '.$row['day'].' - '.$row['year'];
                        $today = color::BOLD.color::AQUA.$row['month'].' - '.$row['day'].' - '.$row['year'];
                        $description = color::AQUA.$row['description'];
                        if ($data === $text || $data === $today) {
                            $content = $text."\n".$description;
                            $event = new ModalGui();
                            $event->setTitle(color::BOLD.$row['name']);
                            $event->setContent($content);
                            $event->setButton1(color::BOLD.color::DARK_GREEN."Menu");
                            $event->setButton2(color::BOLD.color::DARK_RED."EXIT");
                            $event->setAction(function (Player $player, $data): void {
                                if ($data) {
                                    \atom\gui\GUI::send($player, "menu");
                                }
                            });
                            \atom\gui\GUI::register("event", $event);
                            \atom\gui\GUI::send($player, "event");
                        }
                    }
                });
                \atom\gui\GUI::register("menu", $menu);
                \atom\gui\GUI::send($player, "menu");
            });
        });
    }

    public static function event_creator(): GUI {
        $gui = new CustomGui();
        $gui->setTitle(color::BOLD.color::DARK_RED."Create Event");
        $gui->addInput("","Event Name");
        $gui->addDropdown("", self::MONTHS);
        $gui->addInput("", "Date");
        $gui->addInput("", "Description");
        $gui->setAction(function (Player $player, array $data): void {
            switch (strtolower($data[1])){
                case "january":
                case "march":
                case "may":
                case "july":
                case "august":
                case "october":
                case "december":
                    if ($data[2] > 31) {
                        self::error($player, "Selected Month does not have " . $data[2] . " days");
                        return;
                    }
                    break;

                case "april":
                case "june":
                case "september":
                case "november":
                    if ($data[2] > 30) {
                        self::error($player, "Selected Month does not have " . $data[2] . " days");
                        return;
                    }
                    break;

                case "february":
                    if ($data[2] > 28) {
                        self::error($player, "Selected Month does not have " . $data[2] . " days");
                        return;
                    }
                    break;
                default:
                    self::error($player, "No month selected");
                    return;
            }

            if (strlen($data[3]) > 255) {
                self::error($player, "Description overflow; Must be less than 255 characters");
                return;
            }
            //var_dump("should not be reached if there is an error!");
            Database::getDatabase()->executeInsert("manager.insert.event", [
                "name" => $data[0],
                "month" => $data[1],
                "day" => $data[2],
                "year" => getdate()['year'],
                "description" => $data[3]
            ]);
            \atom\gui\GUI::send($player, "success");
        });
        return $gui;
    }

    public static function admin(): GUI {
        $form = new SimpleGui();
        $form->setTitle(color::BOLD.color::DARK_RED."Administrator Management");
        $form->addButton("View Administrators");
        $form->addButton("Add Administrator");
        $form->setAction(function (Player $player, $data): void {
            switch ($data) {
                case "Add Administrator":
                    \atom\gui\GUI::register("add_admin", self::addAdmin());
                    \atom\gui\GUI::send($player, "add_admin");
                    break;
                case "View Administrators":
                    Database::getDatabase()->executeSelect("manager.select.all", [
                    ], function (array $rows) use($player) : void {
                        $gui = new ModalGui();
                        $gui->setTitle(color::BOLD.color::DARK_RED."View Administrators");
                        $array = [];
                        for ($i = 1; $i < count($rows)+1; $i++){
                            array_push($array, $rows[$i-1]["name"]);
                            $content = implode($array, "\n");
                            $gui->setContent($content);
                        }
                        $gui->setButton1(color::BOLD."Menu");
                        $gui->setButton2(color::BOLD.color::DARK_RED."Exit");
                        $gui->setAction(function (Player $player, $data): void {
                            if ($data){
                                \atom\gui\GUI::send($player, "admin");
                            }
                        });
                        \atom\gui\GUI::register("get_admins", $gui);
                        \atom\gui\GUI::send($player, "get_admins");
                    });
                    break;
            }
        });
        return $form;
    }

    public static function addAdmin(): GUI {
        $player_list = [];
        $player_obj = Main::getInstance()->getServer()->getOnlinePlayers();
        foreach ($player_obj as $player) array_push($player_list, $player->getName());
        $form = new CustomGui();
        $form->setTitle(color::BOLD.color::DARK_RED."Add Administrator");
        $form->addDropdown("\nPlease Select Player from List\n", $player_list);
        $form->setAction(function (Player $player, $data): void {
            $uuid = Main::getInstance()->getServer()->getPlayerExact($data[0])->getUniqueId();
            Database::getDatabase()->executeSelect("manager.select.admin", [
                "uuid" => $uuid->toString()
            ], function (array $rows) use ($player, $data, $uuid): void {
                $confirmation = new ModalGui();
                $confirmation->setTitle(color::DARK_RED."Add Administrator");
                $confirmation->setButton1(color::BOLD."Menu");
                $confirmation->setButton2(color::BOLD.color::DARK_RED."Exit");
                foreach ($rows as $row) {
                    if ($row['COUNT(*)'] !== 0) {
                        $confirmation->setContent(color::DARK_AQUA.$data[0].color::DARK_RED." already has permissions to create events");
                    } else {
                        Database::getDatabase()->executeInsert("manager.insert.admin", [
                            "name" => $data[0],
                            "uuid" => $uuid->toString()
                        ]);
                        $confirmation->setContent(color::DARK_AQUA.$data[0].color::DARK_RED." can now create events!");
                    }
                    $confirmation->setAction(function (Player $player, $data): void {
                        if ($data){
                            \atom\gui\GUI::send($player, "admin");
                        }
                    });
                    \atom\gui\GUI::register("admin_confirm", $confirmation);
                    \atom\gui\GUI::send($player, "admin_confirm");
                }
            });
        });
        return $form;
    }

    public static function success(): GUI {
        $gui = new ModalGui();
        $gui->setTitle(color::BOLD.color::DARK_GREEN."SUCCESS");
        $gui->setContent("Successfully created event!");
        $gui->setButton1(color::BOLD.color::DARK_GREEN."Menu");
        $gui->setButton2(color::BOLD.color::DARK_RED."EXIT");
        $gui->setAction(function (Player $player, $data): void {
            self::menu($player);
        });
        return $gui;
    }

    public static function error(Player $player, string $error): GUI {
        $gui = new ModalGui();
        $gui->setTitle(color::BOLD.color::DARK_RED."ERROR");
        $gui->setContent($error);
        $gui->setButton1(color::BOLD.color::DARK_GREEN."Try Again");
        $gui->setButton2(color::BOLD.color::DARK_RED."EXIT");
        $gui->setAction(function (Player $player, $data): void {
            if ($data) \atom\gui\GUI::send($player, "event_creator");
        });
        \atom\gui\GUI::register("error", $gui);
        \atom\gui\GUI::send($player, "error");
        return $gui;
    }
}
