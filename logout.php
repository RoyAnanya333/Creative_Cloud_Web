<?php
session_start();
session_unset();
session_destroy();
header("Location: guest/login.php");
exit();
?>
