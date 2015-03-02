<?php
        define("IN_SYSTEM", 1);

        require_once("lib/database.php");
        require_once("lib/user.php");
        
        error_reporting(E_ALL ^ E_NOTICE);

        $db = Database::getInstance();
        $user = User::getInstance();

        $success = 0;
        $errors = array();

        // If this page is accessed as register script after form filling
        if ($_GET["act"] == "register") {
                
                $success = 1;

                try {
                        $user->register($_POST);
                } catch (UserException $e) {
                        $errors = $e->messages;
                        $success = 0;
                } catch (DatabaseException $e) {
                        die ($e->message);
                }

        }
?>

<!DOCTYPE html>
<html>
        <head>
                <title>Регистрация нового пользователя</title>
                <link rel="stylesheet" type="text/css" href="css/pages.css" />
        </head>

        <body>
                <div class="center-container">
                <h1>Регистрация нового пользователя</h1>

                <?php if (count($errors) > 0) : ?>
                        <div class="error-container">
                                <?php foreach ($errors as $error) print $error . "<br />\n"; ?>
                        </div>
                <?php endif; ?>

                <?php if ($success) : ?>
                        <div class="success-container">
                                Регистрация прошла успешно. Теперь вы можете <a href="login.php">войти</a> в систему.
                        </div>
                <?php else : ?>
                        <form action="register.php?act=register" method="post" class="form" id="reg-form" accept-charset="utf-8">
                                
                                <label for="login" class="isolate">Логин: <input type="text" name="login" value="<?php echo $_POST["login"]; ?>" /></label>
                                <label for="email" class="isolate">E-mail: <input type="text" name="email" value="<?php echo $_POST["email"]; ?>" /></label>
                                
                                <label for="password">Пароль:<input type="password" name="password" value="<?php echo $_POST["password"]; ?>" /></label>
                                <label for="password-match" class="isolate outline">Пароль ещё раз:<input type="password" name="password-match" value="<?php echo $_POST["password-match"]; ?>" /></label>

                                <label for="name">Имя: <input type="text" name="name" value="<?php echo $_POST["name"]; ?>" /></label>
                                <label for="name">Фамилия: <input type="text" name="sname" value="<?php echo $_POST["sname"]; ?>" /></label>
                                <label for="name" class="isolate">Номер класса: <input type="text" name="class" value="<?php echo $_POST["class"]; ?>" /></label>
                                <input type="submit" value="Зарегистрироваться"/>
                        </form>
                <?php endif; ?>
                </div>
        </body>
</html>
