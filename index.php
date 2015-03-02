<?php
        define("IN_SYSTEM", 1);

        require_once("lib/user.php");

        $user = User::getInstance();

        if ($user->getUID() !== null)
                header("Location: /manager.php");
        else
                header("Location: /login.php");
