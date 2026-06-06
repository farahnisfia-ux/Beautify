<?php
session_start();
session_unset();
session_destroy();
header("Location: /beautify/login.php");
exit;
