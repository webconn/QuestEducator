<?php

        define("IN_SYSTEM", 1);
        require_once("lib/database.php");
        require_once("lib/user.php");
        require_once("lib/pages.php");

        error_reporting(E_ALL ^ E_NOTICE);

        $user = User::getInstance();
        $pages = Pages::getInstance();

        $content = "";
        $title = "Менеджер заданий";
        $nav = "";

        try {
                $nav = $pages->getNavigation($_GET);
                $content = $pages->getPage($_GET);
                $title = $pages->getTitle($_GET);
        } catch (PageException $e) {
                if ($e->code == 404) {
                        $content = "<h1>Страница не найдена (404)</h1>";
                        $title = "404";
                }
        }
?>

<!DOCTYPE html>

<html>
        <head>
                <title><?php echo $title; ?></title>
                <link rel="stylesheet" type="text/css" href="css/pages.css" />
                <link rel="stylesheet" type="text/css" href="css/sidebar.css" />
                <link rel="stylesheet" type="text/css" href="css/tasks.css" />
                <?php echo $pages->head; ?>
        </head>
        <body>
                <div id="sidebar">
                        <ul class="top">
                                <li class="<?php if ($_GET["act"] == "topic") echo "active"; ?>"><a href="/manager.php?act=topic"><img src="/img/tasks.png" />Задачи</a></li>
                                <li><a href="/manager.php?act=achievments"><img src="/img/achievments.png" />Успехи</a></li>
                                <?php if ($user->isAdmin()) : ?><li><a href="/editor.php"><img src="/img/editor.png" />Редактор</a></li><?php endif; ?>
                        </ul>

                        <ul class="bottom">
                                <li><a href="/manager.php?act=help"><img src="/img/help.png" />Помощь</a></li>
                                <li><a href="/login.php?act=logout"><img src="/img/logout.png" />Выйти</a></li>
                        </ul>
                </div>

                <div id="content">
                        <?php if ($nav != "") : ?><div id="navbar"><i>Вы здесь:</i> <?php echo $nav; ?></div><?php endif; ?>

                        <?php echo $content; ?>

                </div>

                
        </body>
</html>
