<?php

// ini buat logout

session_start();

session_unset();

session_destroy();

header("Location: login.php");
exit(); 
?>

// test