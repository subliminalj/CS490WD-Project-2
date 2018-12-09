<?php
include 'connection.php';
include "sanitization.php";
$return = "fail"; //value that is returned

if (isset($_POST['name']) && isset($_POST['password'])) {
    //data is sanitized
    $username = sanitizeMYSQL($connection,$_POST['name']);
    $password = md5($salt . sanitizeMYSQL($connection,$_POST['password'])); //sencrypt using salt and md5 function
    $salt="web";
    

    $query = "SELECT * FROM Customer WHERE ID='" . $username . "' AND Password='" . $password . "'";
    $result = mysqli_query($connection,$query);
    if ($result) {
        $row_count = mysqli_num_rows($result);
        if ($row_count == 1) { 
            $row = mysqli_fetch_array($result);
            session_start(); //starts session
            $_SESSION['start'] = time(); //date set for expiration purposes
            $_SESSION["username"] = $row["ID"];  //customer id is saved
            ini_set('session.use_only_cookies',1); //cookies are used to prevent session hijacking
            $return=  "success"; //returns success message
        }
    }
}

    echo $return;



