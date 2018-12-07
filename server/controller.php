<?php
include 'connection.php';
include 'sanitization.php';
session_start(); //start the session
$result = "";


//only process the data if there a request was made and the session is active
if (isset($_POST["type"])) {
    session_regenerate_id(); //regenerate the session to prevent fixation
    $_SESSION['start'] = time(); //reset the session start time
    $request_type = sanitizeMYSQL($connection, $_POST['type']);


    switch ($request_type) { //check the request type
        //calls search function
        case "search":
            $result = search_cars($connection);
            break;
        //gets cars rented
        case "rented":
            $result = get_rented($connection);
            break;
        //gets rental history
        case "history":
            $result = get_history($connection);
            break;
        //rents car
        case "rent":
            $result=rent_car($connection,$_POST['return_id']);
            break;
        case "return":
            $result=return_car($connection,$_POST['return_id'] );
            break;
        case "logout":
            logout();
            $result="success";
            break;
    }
}
    function search_cars($connection) {
    if (isset($_POST['search']) && trim($_POST['search']) != "") {
    //data is sanatized
    $data = sanitizeMYSQL($connection, $_POST['search']);
    //$words = explode(" ", $data);
    //$LIKE = "";
    //$LIKE.=LIKE("FirstName", $words);
    //$LIKE.=" OR " . LIKE("LastName", $words);
   
    }
    $final = array();
    $final["search_car"] = array();
    $query = "SELECT cs.make,cs.yearmade, c.id,cs.model,c.picture_type, c.picture,cs.size,c.picture,c.picture_type,c.color,c.status FROM carspecs cs inner join car c WHERE cs.make LIKE '$data' OR cs.yearmade LIKE '$data' OR cs.model LIKE '$data' or cs.size like '$data' or c.color like '$data'";
    $result = mysqli_query($connection, $query);
    //if no results return
    if (!$result)
        return json_encode($array);
    else {
        $row_count = mysqli_num_rows($result);
        for ($i = 0; $i < $row_count; $i++) {
            $row = mysqli_fetch_array($result);
            $array = array();
            //$array["picture"] = 'data:' . $row["Picture_type"] . ';base64,' . base64_encode($row["Picture"]);
            $array["make"] = $row["make"];
            $array["picture"] = 'data:' . $row["picture_type"] . ';base64,' . base64_encode($row["picture"]);
            $array["model"] = $row["model"];
            $array["year"] = $row["yearmade"];
            $array["size"] = $row["size"];
            $array["ID"] = $row["id"];
            $array["color"] = $row["color"];
            $final["search_car"][] = $array;
        }
    }
    return json_encode($final);
}

function rent_car($connection) {
    $id=$_POST['id'];
    $query1 = "INSERT INTO rental(rentDate, returnDate, status, CustomerID, carID) VALUES(CURDATE(), NULL, '2', '" . $_SESSION["username"] . "', '$id')" ;
        $result1 = mysqli_query($connection, $query1);
    if (!$result1){
        return "fail";
    }
    $query2 = "UPDATE car INNER JOIN rental ON car.ID=rental.carID SET car.status= '2' WHERE car.ID='$id'";
    $result2 = mysqli_query($connection, $query2);
    if (!$result2){
        return "fail";
    }
    return "success";
}

function return_car($connection) {
    $id=$_POST['return_id'];
    $query1 = "UPDATE rental SET status='1', returnDate=CURDATE() WHERE ID='$id'" ;
        $result1 = mysqli_query($connection, $query1);
    if (!$result1){
        return "fail";
    }
    $query2 = "UPDATE car INNER JOIN rental ON car.ID=rental.carID SET car.status= '1' WHERE rental.ID='$id'";
    $result2 = mysqli_query($connection, $query2);
    if (!$result2){
        return "fail";
    }
    return "success";
}

    function get_rented($connection) {
    $final = array();
    $final["rented_car"] = array();
    //write a query about the enrolled courses for that student. The student ID is from the session
    $query = "select cs.id, c.Picture, c.picture_type, cs.make,yearmade,cs.model,cs.size,c.status, cm.`Name`, r.`ID` as rentalid, r.`rentdate`
                from carspecs cs
                inner join car c on c.`CarSpecsID`=cs.`ID`
                inner join rental r on r.`carID`=c.id
                inner join customer cm on cm.id=r.`CustomerID`
                where cm.id='" . $_SESSION["username"] . "' and c.status=2";
    $result = mysqli_query($connection, $query);
    $text = "";
    if (!$result)
        return json_encode($array);
    else {
        $row_count = mysqli_num_rows($result);
        for ($i = 0; $i < $row_count; $i++) {
            $row = mysqli_fetch_array($result);
            $array = array();
            $array["make"] = $row["make"];
            $array["model"] = $row["model"];
            $array["picture"] = 'data:' . $row["Picture_type"] . ';base64,' . base64_encode($row["Picture"]);
            $array["year"] = $row["yearmade"];
            $array["size"] = $row["size"];
            $array["rental_ID"] = $row["rentalid"];
            $array["rent_date"] = $row["rentdate"];
            $final["rented_car"][] = $array;
        }
    }
    return json_encode($final);
}
function get_history($connection) {
    $final = array();
    $final["returned_car"] = array();
    $query = "select cs.id,c.Picture, c.picture_type, cs.make,yearmade,cs.model,cs.size,c.status,  r.`ID` as rentalid, r.`returndate`
                from carspecs cs
               inner join car c on c.`CarSpecsID`=cs.`ID`
                inner join rental r on r.`carID`=c.id
                inner join customer cm on cm.id=r.`CustomerID`
                where c.status=1
                and cm.id='" . $_SESSION["username"] . "'";
    $result = mysqli_query($connection, $query);
    $text = "";
    if (!$result)
        return json_encode($array);
    else {
        $row_count = mysqli_num_rows($result);
        for ($i = 0; $i < $row_count; $i++) {
            $row = mysqli_fetch_array($result);
            $array = array();
            $array["make"] = $row["make"];
            $array["model"] = $row["model"];
            $array["picture"] = 'data:' . $row["Picture_type"] . ';base64,' . base64_encode($row["Picture"]);
            $array["year"] = $row["yearmade"];
            $array["size"] = $row["size"];
            $array["rental_ID"] = $row["rentalid"];
            $array["return_date"] = $row["returndate"];
            $final["returned_car"][] = $array;
        }
    }
    return json_encode($final);
}
function logout() {
    // Unset all of the session variables.
    $_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
        );
    }

// Finally, destroy the session.
    session_destroy();
}

echo $result;
