<?php
include 'connection.php';
include "sanitization.php";
$return = "fail"; //the value that is returned to Ajax

if (isset($_POST['name']) && isset($_POST['password'])) {
    $username = sanitizeMYSQL($connection,$_POST['name']);
    $password = md5($salt . sanitizeMYSQL($connection,$_POST['password'])); //sencrypt it
    $salt="web";
    

    $query = "SELECT * FROM Customer WHERE ID='" . $username . "' AND Password='" . $password . "'";
    $result = mysqli_query($connection,$query);
    if ($result) {
        $row_count = mysqli_num_rows($result);
        if ($row_count == 1) { //start a session
            $row = mysqli_fetch_array($result);
            session_start(); //we start a session
            $_SESSION['start'] = time(); //we set that to make the session expire after some time
            $_SESSION["username"] = $row["ID"];  //we save the student ID here
            ini_set('session.use_only_cookies',1); //use cookies only, prevent session hijacking
            $return=  "success"; //login succeeded
        }
    }
}

    echo $return;
