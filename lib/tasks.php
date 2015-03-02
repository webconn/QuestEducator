<?php

defined("IN_SYSTEM") or die ("Don't touch this");

require_once("database.php");
require_once("pages.php");

class Task {
        
        private $db = null;
        public $id = 0;
        public $level = 0;
        public $topic = 0;
        public $page = 0;
        public $is_starter = 0;
        public $name = "";

        public function __construct($id) {
                $this->db = Database::getInstance();
                $this->id = $id;

                $q = "SELECT `name`, `level`, `topic`, `page` FROM `tasks` WHERE `id`=" . $this->db->escape_string($id) . ";";
                $result = $this->db->query($q);

                if (!$result) throw new DatabaseException($this->db->error, $q);
                if ($result->num_rows == 0) throw new PageException(404);
                $result = $result->fetch_array(MYSQLI_ASSOC);

                $this->level = $result["level"];
                $this->topic = $result["topic"];
                $this->page = $result["page"];
                $this->name = $result["name"];
        }

        public function acceptUserProgress($uid, $result = null) {
                if ($result === null)
                        $result = $this->level;

                // 1. check if this user had a result
                $q = "SELECT `result` FROM `progress` WHERE `uid`=" . $this->db->escape_string($uid) . " AND `task_id` =" . $this->db->escape_string($this->id) . ";";
                $result = $this->db->query($q);
                if (!$result) throw new DatabaseException($this->db->error, $q);

                if ($result->num_rows == 0) {
                        $q = "INSERT INTO `progress` (`uid`, `task_id`, `result`) VALUES (" . $this->db->escape_string($uid) . ", " . $this->db->escape_string($this->id) .
                                ", " . $this->db->escape_string($result) . ");";
                } else {
                        $q = "UPDATE `progress` SET `result` = " . $this->db->escape_string($result) . " WHERE `uid` = " . $this->db->escape_string($uid) . " AND " . 
                                "`task_id` = " . $this->db->escape_string($this->id) . ";";
                }

                $result = $this->db->query($q);
                if (!$result) throw new DatabaseException($this->db->error, $q);
        }

        public function getTopic() {
                return new Topic($this->topic);
        }

        public function getResults($uid) {
                $q = "SELECT `result` FROM `progress` WHERE `uid`=" . $this->db->escape_string($uid) . " AND `task_id` = " . $this->id . ";";

                $result = $this->db->query($q);
                if (!$result) throw new DatabaseException($this->db->error, $q);

                return intval($result->fetch_array(MYSQLI_ASSOC)["result"]);
        }

}
