<?php

defined("IN_SYSTEM") or die("Don't touch this");

require_once("database.php");
require_once("user.php");
require_once("topics.php");

class PageException extends Exception {
        public $code = 200;
        
        public function __construct($c) {
                $this->code = $c;
        }
}

class Pages {

        static private $instance = null;

        static public function getInstance() {
                if (self::$instance == null) {
                        self::$instance = new Pages();
                }
                return self::$instance;
        }

        private $db = null;
        private $user = null;

        public function __construct() {
                $this->db = Database::getInstance();
                $this->user = User::getInstance();
        }

        public function getPage($get) {
                switch ($get["act"]) {
                        case "": /* default page */
                                return $this->getPageByID(1);
                        case "page":
                                return $this->getPageByID($get["id"]);
                        case "topic":
                                return $this->getTopic($get["id"]);
                        case "task":
                                return $this->getTask($get["id"]);
                        default:
                                throw new PageException(404);
                }
        }

        public function getTopic($id) {
                $topic = new Topic($id);

                $ret = "<h1>Просмотр темы: " . $topic->name . "</h1>";

                if ($id != 0) {
                        $ret .= "<a href=\"/manager.php?act=topic&id=" . $topic->parent . "\" class=\"back\">Предыдущая тема</a>";
                }

                $ret .= "<p>Ваш результат в этой теме: <b>" . $topic->getResults($this->user->getUID()) . "</b></p>";


                $subtopics = $topic->getSubTopics();
                
                if (count($subtopics) != 0) {
                        $ret .= "<h2>Подтемы</h2>";
                        $ret .= "<ul class=\"subtopics\">";
                        foreach ($subtopics as $subtopic) {
                                if ($subtopic->isAvailable($this->user->getUID()))
                                        $ret .= "<li class=\"topic-actual\"><a href=\"/manager.php?act=topic&id=" . $subtopic->id . "\">" . $subtopic->name . "</a></li>";
                                else 
                                        $ret .= "<li class=\"topic-unavailable\">" . $subtopic->name . "</li>";
                        }
                        $ret .= "</ul>";
                }


                $tasks = $topic->getTasks();
                if (count($tasks) != 0) {
                        $ret .= "<h2>Задачи</h2>";

                        $ret .= "<ul>";
                        foreach ($tasks as $task) {
                                $ret .= "<li class=\"task-level-" . $task->level . "\"><a href=\"/manager.php?act=task&id=" . $task->id . "\">" . $task->name . "</a> (" . $task->getResults($this->user->getUID()) . 
                                        "/" . $task->level . ")</li>";
                        }
                        $ret .= "</ul>";
                }

                return $ret;
        }

        public function getTitle($get) {
                switch ($get["act"]) {
                        case "": /* default page */
                                return $this->getPageTitleByID(1);
                        case "page":
                                return $this->getPageTitleByID($get["id"]);
                        case "topic":
                                return "Просмотр темы";
                        case "task":
                                return "Задача";
                        default:
                                throw new PageException(404);
                }
        }

        public function getPageByID($page_id) {
                $q = "SELECT `content` FROM `pages` WHERE `id`=" . $this->db->escape_string($page_id) . ";";
                $result = $this->db->query($q);
                if (!$result) throw new DatabaseException($this->db->error, $q);
                if ($result->num_rows == 0) throw new PageException(404);
                return $result->fetch_array(MYSQL_NUM)[0];
        }

        public function getPageTitleByID($page_id) {
                $q = "SELECT `title` FROM `pages` WHERE `id`=" . $this->db->escape_string($page_id) . ";";
                $result = $this->db->query($q);
                if (!$result) throw new DatabaseException($this->db->error, $q);
                if ($result->num_rows == 0) throw new PageException(404);
                return $result->fetch_array(MYSQL_NUM)[0];
        }

        public function getNavigation($get) {

        }

        public function getPageList() {
                $q = "SELECT `id`, `title` FROM `pages`;";
                $result = $this->db->query($q);

                if (!$result) throw new DatabaseException($this->db->error, $q);

                return $result;
        }

        public function deletePage($id) {
                $q = "DELETE FROM `pages` WHERE `id`=" . $this->db->escape_string($id) . ";";
                $result = $this->db->query($q);
                if (!$result) throw new DatabaseException($this->db->error, $q);
        }

        public function getTask($id) {
                $task = new Task($id);

                $out = $this->getPageByID($task->page);

                return $out;
        }

}


