<?php

defined("IN_SYSTEM") or die("Don't touch this");

require_once("pages.php");
require_once("database.php");
require_once("user.php");
require_once("tasks.php");

class Topic {
 
        private $db = null;

        public $id = 0;
        public $name = "Все темы";
        public $parent = 0;
        public $threshold = 0;

        public function __construct($id = 0) { // id == 0 => root topic (dummy)
                $this->db = Database::getInstance();
                $this->id = intval($id);

                if ($id != 0) {
                        $q = "SELECT `name`, `parent`, `threshold` FROM `topics` WHERE `id`=" . $this->db->escape_string($id) . ";";
                        $result = $this->db->query($q);
                        if (!$result) throw new DatabaseException($this->db->error, $q);

                        $result = $result->fetch_array(MYSQLI_ASSOC);

                        $this->name = $result["name"];
                        $this->parent = $result["parent"];
                        $this->threshold = $result["threshold"];
                }
        }

        public function getSubTopics() {
                $q = "SELECT `id` FROM `topics` WHERE `parent`=" . $this->db->escape_string($this->id) . ";";
                $result = $this->db->query($q);
                if (!$result) throw new DatabaseException($this->db->error, $q);

                $ret = array();

                if ($result) {
                        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                                $ret[] = new Topic($row["id"]);
                        }
                }

                return $ret;
        }

        public function getParentTopic() {
                if ($this->id != 0)
                        return new Topic($this->parent);
        }

        public function getTasks() {
                $ret = array();

                $q = "SELECT `id` FROM `tasks` WHERE `topic`=" . $this->id . ";";

                $result = $this->db->query($q);
                if (!$result) throw new DatabaseException($this->db->error, $q);

                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                        $ret[] = new Task($row["id"]);
                }

                return $ret;
        }
        
        public function getResults($uid) {
                $sum = 0;

                // 1. get all tasks results
                $q = "SELECT `id` FROM `tasks` WHERE `topic`=" . $this->id . ";";

                $result = $this->db->query($q);
                if (!$result) throw new DatabaseException($this->db->error, $q);

                if ($result) {
                        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                                $q = "SELECT `result` FROM `progress` WHERE `task_id`=" . $row["id"] . " AND `uid`=" . $this->db->escape_string($uid) . ";";
                                print "\n";
                                
                                $sum += $this->db->query($q)->fetch_array(MYSQLI_ASSOC)["result"];
                        }
                }

                // 2. get results from subtopics
                $subtopics = $this->getSubTopics();

                foreach ($subtopics as $sub) {
                        $sum += $sub->getResults($uid);
                }

                return $sum;
        }

        public function isAvailable($uid) {
                $result = $this->getParentTopic()->getResults($uid);

                if ($result < $this->threshold)
                        return false;
                else
                        return true;
        }

}
/*
class Topics {
        
        static private $instance = null;

        static public function getInstance() {
                if (self::$instance == null) {
                        self::$instance = new Topics();
                }
                return self::$instance;
        }

        private $db;
        private $pages;
        private $user;

        private function __construct() {
                $this->db = Database::getInstance();
                $this->pages = Pages::getInstance();
                $this->user = User::getInstance();
        }

        // Class definition

        public function getRootTopic() {
                
        }

        public function getTopic($topic_id) {
                
        }

};
*/
