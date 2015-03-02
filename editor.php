<?php

        define("IN_SYSTEM", 1);
        require_once("lib/database.php");
        require_once("lib/user.php");
        require_once("lib/pages.php");
        require_once("lib/editor.php");

        error_reporting(E_ALL ^ E_NOTICE);

        $user = User::getInstance();
        $pages = Pages::getInstance();
        $editor = Editor::getInstance();

        $title = "";
        $content = "";

        if ($user->getGID() != "admin")
                die("403 Forbidden");

        switch ($_GET["act"]) {
                case "edit":
                        $title = "Редактирование страницы";
                        $content = $editor->editPage($_GET);
                        break;
                case "new":
                        $title = "Создание новой страницы";
                        $content = $editor->createPage();
                        break;
                case "create":
                        $content = $editor->savePage($_POST);
                        break;
                case "update":
                        $content = $editor->updatePage($_POST);
                        break;
                case "delete":
                        $title = "Удаление страницы";
                        $content = $editor->deletePage($_GET);
                        break;
                case "":
                case "select":
                        $title = "Выбор страницы для редактирования";
                        $content = "<a href=\"/editor.php?act=topics\" class=\"topics\">Перейти в редактор тем</a>";
                        $content .= "<a href=\"/editor.php?act=new\" class=\"newpage\">Создать новую страницу</a>";
                        $content .= $editor->pagesList();
                        break;

                case "topics":
                        $title = "Выбор темы для редактирования";
                        $content = "<a href=\"/editor.php?act=new-topic\" class=\"newpage\">Создать новую тему</a>";
                        $content .= $editor->topicsList();
                        break;

                case "edit-topic":
                        $title = "Редактирование темы";
                        $content = $editor->editTopic($_GET["id"]);
                        break;

                case "new-topic":
                        $title = "Создание новой темы";
                        $content = $editor->newTopic();
                        break;

                case "delete-topic":
                        $title = "Удаление темы";
                        $content = $editor->deleteTopic($_GET);
                        break;

                case "save-topic":
                        $title = "Сохранение новой темы";
                        $content = $editor->topicSave($_POST);
                        break;

                case "update-topic":
                        $title = "Сохранение темы";
                        $content = $editor->topicUpdate($_POST);
                        break;

                default:
                        $content = "Страница не найдена (404)";

        }
?>

<!DOCTYPE html>

<html>
        <head>
                <title>Редактор</title>
                <link rel="stylesheet" type="text/css" href="css/pages.css" />
                <link rel="stylesheet" type="text/css" href="css/sidebar.css" />
                <link rel="stylesheet" type="text/css" href="css/editor.css" />
        </head>
        <body>
                <div id="sidebar">
                        <ul class="top">
                                <li><a href="/manager.php?act=topic"><img src="/img/tasks.png" />Задачи</a></li>
                                <li><a href="/manager.php?act=achievments"><img src="/img/achievments.png" />Успехи</a></li>
                                <?php if ($user->isAdmin()) : ?><li class="active"><a href="/editor.php"><img src="/img/editor.png" />Редактор</a></li><?php endif; ?>
                        </ul>

                        <ul class="bottom">
                                <li><a href="/manager.php?act=help"><img src="/img/help.png" />Помощь</a></li>
                                <li><a href="/login.php?act=logout"><img src="/img/logout.png" />Выйти</a></li>
                        </ul>
                </div>

                <div id="content">
                        <h1><?php echo $title; ?></h1>

                        <?php echo $content; ?>

                </div>

                
        </body>
</html>
