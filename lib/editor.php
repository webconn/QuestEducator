<?php

        defined("IN_SYSTEM") or die("Don't touch this");

        require_once("database.php");
        require_once("user.php");
        require_once("pages.php");

class Editor {
        
        static private $instance = null;

        static public function getInstance() {
                if (self::$instance == null) {
                        self::$instance = new Editor();
                }
                return self::$instance;
        }       


        private $db = null;
        private $pages = null;
        private $user = null;

        private function __construct() {
                $this->db = Database::getInstance();
                $this->pages = Pages::getInstance();
                $this->user = User::getInstance();
        }

        public function pagesList() {
                $out = "";

                $out .= "<table id=\"pagelist\">";

                $out .= "<tr class=\"head\"><th class=\"pageid\">ID</th><th class=\"title\">Заголовок</th><th class=\"links\">Действия</th></tr>";

                $list = $this->pages->getPageList();
                $even = 0;

                while ($row = $list->fetch_array(MYSQLI_ASSOC)) {
                        $out .= "<tr class=\"" . ($even ? "even" : "odd") . "\">";
                        $even = !$even;

                        $out .= "<td class=\"pageid\">" . $row["id"] . "</td>";
                        $out .= "<td class=\"title\"><a href=\"/manager.php?act=page&id=" . $row["id"] . "\" target=\"_blank\" class=\"page\">" . $row["title"] . "</a></td>";
                        $out .= "<td class=\"links\"><a href=\"/editor.php?act=edit&id=" . $row["id"] . "\" class=\"edit\">Редактировать</a> ";
                        if ($row["id"] != 1) 
                                $out .= "<a href=\"/editor.php?act=delete&id=" . $row["id"] . "\" class=\"delete\">Удалить</a></td></tr>";
                }

                $out .= "</table>";

                return $out;
        }

        public function deletePage($get) {
                $out = "";

                if ($get["confirm"] == "y") {
                        $this->pages->deletePage($get["id"]);
                        $out .= "Страница " . $get["id"] . " была успешно удалена. <a href=\"/editor.php\">Вернуться в редактор</a>";
                } else {
                        $out .= "<p class=\"confirmer\">Вы действительно хотите удалить страницу " . $get["id"] . "?</p>";
                        $out .= "<p class=\"confirmer-choose\"><a href=\"/editor.php?act=delete&id=" . $get["id"] . "&confirm=y\">Да</a> <a href=\"/editor.php\">Нет</a></p>";
                }

                return $out;
        }

        public function editPage($get) {
                return $this->editorForm("edit", $get["id"]);
        }

        public function createPage() {
                return $this->editorForm("new");
        }

        public function updatePage($post) {
                $q = "UPDATE `pages` SET `title`=\"" . $this->db->escape_string($post["title"]) . "\", `content`=\"" 
                        . $this->db->escape_string($post["content"]) . "\" WHERE `id`=" . $this->db->escape_string($post["id"]) . ";";

                if (!$this->db->query($q)) throw new DatabaseException("Ошибка БД: " . $this->db->error);

                return "Страница успешно сохранена. <a href=\"/editor.php\">Вернуться в редактор</a>";
        }

        public function savePage($post) {
                $q = "INSERT INTO `pages` (`title`, `content`) VALUES (\"" . $this->db->escape_string($post["title"]) . "\", \"" 
                        . $this->db->escape_string($post["content"]) . "\");";
                
                if (!$this->db->query($q)) throw new DatabaseException("Ошибка БД: " . $this->db->error);

                return "Страница успешно сохранена. <a href=\"/editor.php\">Вернуться в редактор</a>";
        }

        public function editorForm($act, $id=0) {
                $out = "";

                $out = <<<SCRIPT
<script src="/js/tinymce/tinymce.min.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript">

tinyMCE.init({

selector: "textarea",
    plugins: [
        "advlist autolink lists link image charmap print preview anchor",
        "searchreplace visualblocks code fullscreen",
        "insertdatetime media table contextmenu paste"
    ],

    formats: {
        code_block : { block : 'div', attributes : { title : "Source code" }, classes : 'code-block' }
    },
    
    toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image code_block",


setup: function(ed) {
    ed.on('keydown', function(event) {
        if (event.keyCode == 9) { // tab pressed
          if (event.shiftKey) {
            ed.execCommand('Outdent');
          }
          else {
            ed.execCommand('Indent');
          }

          event.preventDefault();
          event.stopPropagation();
          return false;
        }
    });
}

 });

</script>
SCRIPT;
                $content = "";
                $title = "";
                
                if ($act == "edit") {
                        $content = $this->pages->getPageByID($id);
                        $title = $this->pages->getPageTitleByID($id);

                        $out .= "<form action=\"/editor.php?act=update\" method=\"post\">";
                } else {
                        $out .= "<form action=\"/editor.php?act=create\" method=\"post\">";
                }

                $out .= "<label for=\"title\">Заголовок: <input type=\"text\" name=\"title\" value=\"" . $title . "\" /></label>";


                $out .= "<textarea cols=\"40\" id=\"editor_content\" name=\"content\" rows=\"20\">" . $content . "</textarea>";
                $out .= "<input type=\"hidden\" name=\"id\" value=\"" . $id . "\" />";
                $out .= "<input type=\"submit\" value=\"Сохранить\" \>";
                $out .= "</form>";

                return $out;
        }

        public function pagesSelector($name, $value) {
                $ret = "<select name=\"" . $name . "\">";
                
                $q = "SELECT `id`, `title` FROM `pages`;";
                $res = $this->db->query($q);
                if (!$res) throw new DatabaseException($this->db->error, $q);

                while ($a = $res->fetch_array(MYSQLI_ASSOC)) {
                        $ret .= "<option value=\"" . $a["id"] . "\"";
                        if ($a["id"] == $value)
                                $ret .= " selected";
                        $ret .= ">" . $a["id"] . " -> " . $a["title"] . "</option>";
                }

                $ret .= "</select>";

                return $ret;
        }

        public function topicsList($id = 0) {
                $topic = new Topic($id);

                $subs = $topic->getSubTopics();

                if (count($subs) == 0)
                        return "";

                $ret = "<ul class=\"topic\">";


                foreach ($subs as $s) {
                        $ret .= "<li><a href=\"/editor.php?act=edit-topic&id=" . $s->id . "\">" . $s->name . "</a></li>";
                        $ret .= $this->topicsList($s->id);
                }

                $ret .= "</ul>";
                return $ret;
        }

        public function deleteTopic($get) {
                if ($get["confirm"] == "y") {
                        $q = "DELETE FROM `topics` WHERE `id`=" . $this->db->escape_string($get["id"]) . ";";
                        if (!$this->db->query($q)) throw new DatabaseException($this->db->error, $q);

                        $q = "UPDATE `topics` SET `parent`=0 WHERE `parent`=" . $this->db->escape_string($get["id"]) . ";";
                        if (!$this->db->query($q)) throw new DatabaseException($this->db->error, $q);

                        $q = "UPDATE `tasks` SET `topic`=0 WHERE `topic`=" . $this->db->escape_string($get["id"]) . ";";
                        if (!$this->db->query($q)) throw new DatabaseException($this->db->error, $q);

                        return "Тема успешно удалена. <a href=\"/editor.php?act=topics\">Вернуться в редактор</a>";
                } else {
                        $ret = "<p class=\"confirmer\">Вы действительно хотите удалить тему " . $get["id"] . "?</p>";
                        $ret .= "<p class=\"confirmer-choose\"><a href=\"/editor.php?act=delete-topic&id=" . $get["id"] . "&confirm=y\">Да</a> <a href=\"/editor.php?act=topics\">Нет</a></p>";
                        return $ret;
                }
        }

        public function editTopic($id) {
                return $this->topicEditForm("edit", $id);
        }

        public function newTopic() {
                return $this->topicEditForm("new");
        }

        public function topicSelector($name, $value) {
                $ret = "<select name=\"" . $name . "\">";

                $ret .= $this->_getSubTopicsSelector(0, "", $value);

                $ret .= "</select>";

                return $ret;
        }

        private function _getSubTopicsSelector($id, $prefix, $value) {
                $topic = new Topic($id);

                $ret = "<option value=\"" . $id . "\"";
                if ($value == $id)
                        $ret .= " selected";
                $ret .= ">" . $prefix . $topic->name . "</option>";

                $subs = $topic->getSubTopics();

                if (count($subs) != 0) {
                        foreach ($subs as $s) {
                                $ret .= $this->_getSubTopicsSelector($s->id, $prefix . "&nbsp;&nbsp;", $value);
                        }
                }

                return $ret;
        }

        public function topicEditForm($act, $id = 0) {
                $topic = null;
                if ($act == "edit") {
                        $topic = new Topic($id);
                }

                $ret = "<form class=\"edit-topic\" action=\"/editor.php?act=" . ($act == "new" ? "save-topic" : "update-topic&id=" . $id ) . "\" method=\"post\">";

                if ($act == "edit") {
                        $ret .= "<input type=\"hidden\" name=\"id\" value=\"" . $id . "\" />";
                }

                $ret .= "<label for=\"name\">Имя темы: <input type=\"text\" name=\"name\"" . ($act == "edit" ? " value=\"" . $topic->name . "\"" : "") . " /></label>";

                /*$ret .= "<label for=\"page\">Страница: " . $this->pagesSelector("page", $act == "edit" ? $topic->page : 0) . "</label>";*/
                $ret .= "<label for=\"parent\">Родительская тема: " . $this->topicSelector("parent", $act == "edit" ? $topic->parent : 0) . "</label>";

                $ret .= "<label for=\"threshold\">Порог по баллам: <input type=\"text\" name=\"threshold\"" . ($act == "edit" ? " value=\"" . $topic->threshold . "\"" : "") . " /></label>";

                $ret .= "<input type=\"submit\" value=\"Сохранить\" />";

                if ($act == "edit" && $topic->id != 0)
                        $ret .= "<a href=\"/editor.php?act=delete-topic&id=" . $topic->id . "\" class=\"delete\">Удалить тему</a>";

                return $ret;
        }

        public function topicUpdate($post) {
                $q = "UPDATE `topics` SET `name`=\"" . $this->db->escape_string($post["name"]) . "\", `parent`=" . $this->db->escape_string($post["parent"]) . ", `threshold`=" . $this->db->escape_string($post["threshold"]) . " WHERE `id`=" . $this->db->escape_string($post["id"]) . ";";

                if (!$this->db->query($q)) throw new DatabaseException($this->db->error, $q);

                return "Тема успешно обновлена. <a href=\"/editor.php?act=topics\">Вернуться в редактор</a>";
        }

        public function topicSave($post) {
                $q = "INSERT INTO `topics` (`name`, `parent`, `threshold`) VALUES (";
                $q .= "\"" . $this->db->escape_string($post["name"]) . "\", ";
                $q .= $this->db->escape_string($post["parent"]) . ", ";
                $q .= $this->db->escape_string($post["threshold"]) . ");";

                if (!$this->db->query($q)) throw new DatabaseException($this->db->error, $q);
                
                return "Тема успешно сохранена. <a href=\"/editor.php?act=topics\">Вернуться в редактор</a>";
        }

        public function tasksList($topic) {
                $ret = "";
                $ret .= "<a href=\"/editor.php?act=new-task\" class=\"newpage\">Создать новое задание</a>";
                $ret .= "<form action=\"/editor.php\" method=\"get\" class=\"topic-selector\">";
                $ret .= "<input type=\"hidden\" name=\"act\" value=\"tasks\" />";
                $ret .= $this->topicSelector("topic", $topic);
                $ret .= "<input type=\"submit\" value=\"Выбрать тему\" />";
                $ret .= "</form>";

                $q = "SELECT `id`, `name`, `topic` FROM `tasks`";

                if ($topic != 0)
                        $q .= "WHERE `topic`=" . $this->db->escape_string($topic) . ";";
                else
                        $q .= ";";

                $res = $this->db->query($q);
                if (!$res) throw new DatabaseException($this->db->error, $q);

                if ($res->num_rows != 0) {
        
                        $ret .= "<table id=\"tasklist\">";
                        $ret .= "<tr><th class=\"id\">ID</th><th class=\"name\">Имя задания</th><th class=\"topic\">Тема</th><th class=\"actions\">Действия</th></tr>";

                        $even = 0;

                        while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                                $ret .= "<tr class=\"" . ($even ? "even" : "odd" ) . "\">";
                                $ret .= "<td class=\"id\">" . $row["id"] . "</td>";
                                $ret .= "<td class=\"name\"><a href=\"/editor.php?act=edit-task&id=" . $row["id"] . "\">";
                                $ret .= $row["name"] . "</a></td>";
                                $ret .= "<td class=\"topic\">" . $row["topic"] . "</td>";
                                $ret .= "<td class=\"actions\">";
                                $ret .= "<a href=\"/editor.php?act=edit-task&id=" . $row["id"] . "\" class=\"edit\">Редактировать</a>";
                                $ret .= "<a href=\"/editor.php?act=delete-task&id=" . $row["id"] . "\" class=\"delete\">Удалить</a>";
                                $ret .= "</td></tr>";

                                $even = !$even;
                        }

                        $ret .= "</table>";
                }

                return $ret;
        }

        public function newTask() {
                return $this->taskEditForm("new");
        }

        public function editTask($id) {
                return $this->taskEditForm("edit", $id);
        }

        public function taskEditForm($act, $id=0) {

                $ret = "<form action=\"/editor.php?act=";
                $task = null;

                if ($act == "edit") {
                        $ret .= "update-task";
                        $task = new Task($id);
                } else {
                        $ret .= "save-task";
                }

                $ret .= "\" method=\"post\" class=\"task-edit\">";

                if ($act == "edit") {
                        $ret .= "<input type=\"hidden\" name=\"id\" value=\"" . $id . "\" />";
                }

                $ret .= "<label for=\"name\">Имя задания: <input type=\"text\" name=\"name\" value=\"";
                $ret .= ($act == "edit" ? $task->name : "");
                $ret .= "\" /></label>";

                $ret .= "<label for=\"level\">Уровень задания: <input type=\"text\" name=\"level\" value=\"";
                $ret .= ($act == "edit" ? $task->level : "");
                $ret .= "\" /></label>";

                $ret .= "<label for=\"topic\">Тема: ";
                $ret .= $this->topicSelector("topic", $act == "edit" ? $task->topic : 0);
                $ret .= "</label>";

                $ret .= "<label for=\"page\">Страница: ";
                $ret .= $this->pagesSelector("page", $act == "edit" ? $task->page : 0);
                $ret .= "</label>";

                $ret .= "<input type=\"submit\" value=\"Сохранить\" />";
                $ret .= "<a href=\"/editor.php?act=tasks\">Вернуться в редактор</a>";

                $ret .= "</form>";

                return $ret;
        }

        public function updateTask($post) {
                $q = "UPDATE `tasks` SET";
                $q .= "`name`=\"" . $this->db->escape_string($post["name"]) . "\", ";
                $q .= "`level`=" . $this->db->escape_string($post["level"]) . ", ";
                $q .= "`topic`=" . $this->db->escape_string($post["topic"]) . ", ";
                $q .= "`page`=" . $this->db->escape_string($post["page"]);
                $q .= " WHERE `id`=" . $this->db->escape_string($post["id"]) . ";";

                if (!$this->db->query($q)) throw new DatabaseException($this->db->error, $q);

                return "Задание успешно обновлено. <a href=\"/editor.php?act=tasks\">Вернуться в редактор</a>";
        }

        public function saveTask($post) {
                $q = "INSERT INTO `tasks` (`name`, `level`, `topic`, `page`) VALUES (";
                $q .= "\"" . $this->db->escape_string($post["name"]) . "\", ";
                $q .= $this->db->escape_string($post["level"]) . ", ";
                $q .= $this->db->escape_string($post["topic"]) . ", ";
                $q .= $this->db->escape_string($post["page"]) . ");";

                if (!$this->db->query($q)) throw new DatabaseException($this->db->error, $q);

                return "Задание успешно сохранено. <a href=\"/editor.php?act=tasks\">Вернуться в редактор</a>";
        }

        public function deleteTask($get) {
                if ($get["confirm"] == "y") {
                        $q = "DELETE FROM `tasks` WHERE `id`=" . $this->db->escape_string($get["id"]) . ";";
                        if (!$this->db->query($q)) throw new DatabaseException($this->db->error, $q);
                        return "Задание успешно удалено. <a href=\"/editor.php?act=tasks\">Вернуться в редактор</a>";
                } else {
                        $ret = "<p class=\"confirmer\">Вы действительно хотите удалить задание " . $get["id"] . "?</p>";
                        $ret .= "<p class=\"confirmer-choose\"><a href=\"/editor.php?act=delete-task&id=" . $get["id"] . "&confirm=y\">Да</a> <a href=\"/editor.php?act=tasks\">Нет</a></p>";
                        return $ret;
                }
        }

};

