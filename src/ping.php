<?php
session_start();
include_once 'function-includes/init.php';

if (!isset($_SESSION['user']['user'])) {
    if(isset($_SERVER['HTTP_AUTHORIZATION'])  && strlen($_SERVER['HTTP_AUTHORIZATION']) > 25){
        $_SESSION['user']['user'] = authUser();
    }
    else {
        header("HTTP/1.1 401 Unauthorized");
        exit();
    }
}
echo $_SESSION['user']['user'];
