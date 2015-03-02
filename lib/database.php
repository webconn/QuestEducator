<?php

/**
 * Database connection class
 * Implemented as singleton
 */

if (!defined("IN_SYSTEM")) die("This is a module. Don't touch this.");

require_once("config.php");

class DatabaseException extends Exception {
        public $error;
        public $query;

        public function __construct($e, $q = "") {
                $this->error = $e;
                $this->query = $q;
        }
}

function db_ex_hndlr(DatabaseException $e) {
        echo "Ошибка БД: " . $e->error . " (по запросу " . $e->query . ")";
}

set_exception_handler('db_ex_hndlr');

class Database {
        
        // Database object instance
        static private $instance = null;

        // Database instance getter
        static public function getInstance() {
                if (self::$instance == null) {
                        self::$instance = new mysqli(CONFIG_MYSQL_SERVER, CONFIG_MYSQL_USER, CONFIG_MYSQL_PASSWORD, CONFIG_MYSQL_DB);
                        self::$instance->set_charset("utf8");
                }

                return self::$instance;
        }

        private function __clone() {}
};
