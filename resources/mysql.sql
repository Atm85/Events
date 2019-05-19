-- #!mysql
-- #{ manager
-- #    { init
-- #        { main
            CREATE TABLE if NOT EXISTS events(
                id int(11) PRIMARY KEY AUTO_INCREMENT,
                name TINYTEXT NOT NULL,
                month TINYTEXT NOT NULL,
                day INT(2),
                year int(4),
                description TINYTEXT NOT NULL
            );
-- #        }
-- #        { admins
            CREATE TABLE if NOT EXISTS admins(
                id int(11) PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(16) UNIQUE NOT NULL ,
                uuid VARCHAR(36) UNIQUE NOT NULL
            );
-- #        }
-- #    }
-- #    { insert
-- #        { event
-- #        :name string
-- #        :month string
-- #        :day int
-- #        :year int
-- #        :description string
            INSERT INTO events(
                name, month, day, year, description
            ) VALUES (
                :name, :month, :day, :year ,:description
            );
-- #        }
-- #        { admin
-- #        :name string
-- #        :uuid string
            INSERT INTO admins(
                name, uuid
            ) VALUES (
                :name, :uuid
            );
-- #        }
-- #    }
-- #    { select
-- #        { events
            SELECT * FROM events;
-- #        }
-- #        { all
            SELECT * FROM admins;
-- #        }
-- #        { admin
-- #        :uuid string
            SELECT COUNT(*) FROM admins WHERE uuid = :uuid;
-- #        }
-- #    }
-- #}