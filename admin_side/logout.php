<?php

// ini buat logout

//fix bug cache logout

session_start();

session_unset();

session_destroy();

header("Location: login.php");
exit(); 
?>

// test