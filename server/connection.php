<?php

$db_hostname = 'kc-sce-appdb01';
$db_database = "jwv75_1";
$db_username = "jwv75_1";
$db_password = "BOhtYjyupqq7PtjfgA95";


$connection = mysqli_connect($db_hostname, $db_username, $db_password, $db_database);

if (!$connection)
    die("Unable to connect to MySQL: " . mysqli_connect_errno());


?>
