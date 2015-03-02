<?php

defined("IN_SYSTEM") or die("Don't touch this.");

require_once("database.php");

class UserException extends Exception {
        public $messages = array();

        public function __construct($m) {
                $this->messages = $m;
        }
}

class User {

        static private $instance = null;

        static public function getInstance() {
                if (self::$instance == null) {
                        self::$instance = new User();
                }

                return self::$instance;
        }

        private $db = null;
        private $uid = null;
        private $gid = null;
        
        private function __construct() {
                $this->db = Database::getInstance();
                session_start();

                $this->uid = $_SESSION["uid"];

                if ($this->uid != null) {
                        $q = "SELECT `grp` FROM `users` WHERE `uid`=" . $this->uid . ";";
                        if (!($q = $this->db->query($q))) throw new DatabaseException("Ошибка БД: " . $this->db->error);

                        $this->gid = $q->fetch_array(MYSQLI_NUM)[0];
                }
        }

        
        public function login($login, $password) {
                
                $errors = array();

                // 1. check login and password
                if ($login == "") $errors[] = "Логин не может быть пустым";
                if ($password == "") $errors[] = "Пароль не может быть пустым";
        

                // 2. ask database about such user
                $q = "SELECT `uid` FROM `users` WHERE `login`=\"" . $this->db->escape_string($login) . "\" and `password`=\"" . md5($password) . "\";";

                $result = $this->db->query($q);

                if (!$result) throw new DatabaseException("Ошибка БД: " . $db->error);
                
                if ($result->num_rows != 1) $errors[] = "Неверное имя пользователя или пароль"; 

                if (count($errors) != 0)
                        throw new UserException($errors);

                // 3. receive UID
                $uid = $result->fetch_array(MYSQLI_ASSOC);
                $this->uid = $uid["uid"];

                // 4. set current user online
                $q = "UPDATE `users` SET `online` = 1 WHERE `uid`=" . $this->uid . ";";
                if (!$this->db->query($q)) throw new DatabaseException("Ошибка БД: " . $db->error);

                $_SESSION["uid"] = $this->uid;

                $this->save_to_log($this->uid, "login");

                return $this->uid;
        }

        public function logout() {
                $_SESSION["uid"] = null;

                $q = "UPDATE `users` SET `online` = 0 WHERE `uid`=" . $this->uid . ";";
                if (!$this->db->query($q)) throw new DatabaseException("Ошибка БД: " . $db->error); // MySQL error excaption

                $this->save_to_log($this->uid, "logout");

                $this->uid = null;
        }

        public function register($post) {
                
                $errors = array();

                // 0. Check if name and password exist
                if ($post["login"] == "") {
                        $errors[] = "Логин не может быть пустым.";
                }
                
                // 1. Check name existance
                else if ($this->db->query("SELECT `login` FROM `users` WHERE `login`=\"" . $this->db->escape_string($post["login"]) . "\"")->num_rows != 0) {
                        $errors[] = "Такой логин уже существует. Выберите другой логин.";
                }

                // 2. Check password existance
                if ($post["password"] == "") {
                        $errors[] = "Пароль не может быть пустым.";
                }

                // 3. Check password match
                else if ($post["password"] != $post["password-match"]) {
                        $errors[] = "Введённые пароли не совпадают.";
                }

                // Now if it's all right, create new user and allow him to log in.
                if (count($errors) == 0) {
                        $q = "INSERT INTO `users` (`login`, `password`, `email`, `name`, `sname`, `class`) VALUES (\"" 
                                . $this->db->escape_string($post["login"]) . "\", \"" 
                                . md5($post["password"]) . "\",\"" 
                                . $this->db->escape_string($post["email"]) . "\", \"" 
                                . $this->db->escape_string($post["name"]) . "\", \"" 
                                . $this->db->escape_string($post["sname"]) . "\", \"" 
                                . $this->db->escape_string($post["class"]) ."\");"; 
                        
                        if (!$this->db->query($q)) {
                                throw new DatabaseException("Ошибка БД: " . $this->db->error);
                        }
                } else {
                        throw new UserException($errors);
                }

        }

        private function save_to_log($uid, $action){
                $q = "INSERT INTO `users_log` (`uid`, `action`) VALUES (" . $uid . ", \"" . $action . "\");";
                if (!$this->db->query($q)) throw new DatabaseException("Ошибка БД: " . $db->error);
        }

        public function getUID() {
                return $this->uid;
        }

        public function getGID() {
                return $this->gid;
        }

        public function isAdmin() {
                return ($this->gid == "admin");
        }
};
