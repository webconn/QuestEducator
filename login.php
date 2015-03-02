<?php

        define("IN_SYSTEM", 1);
        require_once("lib/database.php");
        require_once("lib/user.php");
        
        error_reporting(E_ALL ^ E_NOTICE);

        $db = Database::getInstance();
        $user = User::getInstance();

        $errors = array();
        $success = 0;

        if ($_GET["act"] == "login" && $user->getUID() == null) {
                
                try {
                        $user->login($_POST["login"], $_POST["password"]);
                        header("Location: /manager.php");
                } catch (UserException $e) {
                        $errors = $e->messages;
                } catch (DatabaseException $e) {
                        die ($e->message);
                }

                

        } else if ($_GET["act"] == "logout" && $user->getUID() != null) {
                $user->logout();
        }
?>

<!DOCTYPE html>

<html>
        <head>
                <title>Вход в систему</title>
                <link rel="stylesheet" type="text/css" href="css/pages.css" />
        </head>
        <body>
                <div class="center-container">
                <?php if ($user->getUID() == null) : ?>
                        <h1>Вход в систему</h1>
                        
                <?php if (count($errors) > 0) : ?>
                        <div class="error-container">
                                <?php foreach ($errors as $error) print $error . "<br />\n"; ?>
                        </div>
                <?php endif; ?>
        
                <?php if (!$success) : ?>
                        <form action="login.php?act=login" method="post" class="form" id="login-form" accept-charset="utf-8">
                                <label for="login">Логин: <input type="text" name="login" value="<?php echo $_POST["login"]; ?>" /></label>
                                <label for="password" class="isolate">Пароль:<input type="password" name="password" value="<?php echo $_POST["password"]; ?>" /></label>

                                <input type="submit" value="Войти" />
                        </form>
                        <br />

                        <a href="/register.php">Зарегистрироваться</a>
                <?php endif; // login form ?>
                <?php else :  // user->getUID() ?>
                        Вы уже совершили вход в систему. <a href="/login.php?act=logout">Выйти</a>
                <?php endif; ?>
                </div>
        </body>
</html>
