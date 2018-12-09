<?php
include 'connection.php';
include 'sanitization.php';
session_start(); //start the session
$result = "";
search_cars($connection);
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
        //rented car function call
        case "rented":
            $result = get_rented($connection);
            break;
        //rental history function call
        case "history":
            $result = get_history($connection);
            break;
        //rents car function call
        case "rent":
            $result=rent_car($connection,$_POST['return_id']);
            break;
        //return car function called
        case "return":
            $result=return_car($connection,$_POST['return_id'] );
            break;
        //logout
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
    //string is spliced by space and stored into array
    $words = explode(" ", $data);
    $LIKE = "";
    //like string is built using like function so user can search with multiple parameters
    $LIKE.=LIKE("cs.make", $words);
    $LIKE.=" OR " . LIKE("cs.yearmade", $words);
    $LIKE.=" OR " . LIKE("cs.model", $words);
    $LIKE.=" OR " . LIKE("cs.size", $words);
    $LIKE.=" OR " . LIKE("c.color", $words);
   
    }
    //final array that will be returned that contains all the search returns
    $final = array();
    $final["search_car"] = array();
   $query = "SELECT cs.make,cs.yearmade, c.id,cs.model,c.picture_type, c.picture,cs.size,c.picture,c.picture_type,c.color,c.status 
    FROM carspecs cs 
    inner join car c on c.carspecsid=cs.id WHERE ($LIKE) and c.status=1";
    $result = mysqli_query($connection, $query);
    //if no results return
    if (!$result)
        return json_encode($array);
    else {
        //data is stored in array which is then stored in the final array, data will be used for template
        $row_count = mysqli_num_rows($result);
        for ($i = 0; $i < $row_count; $i++) {
            $row = mysqli_fetch_array($result);
            $array = array();
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
//rents car
function rent_car($connection) {
    //data is sanatized for security
    $id=sanitizeMYSQL($connection, $_POST['rental_id']);
    //rental table update
    $query1 = "INSERT INTO rental(rentDate, returnDate, status, CustomerID, carID) VALUES(CURDATE(), NULL, '2', '" . $_SESSION["username"] . "', '$id')" ;
        $result1 = mysqli_query($connection, $query1);
    if (!$result1){
        return "fail";
    }
    //car table update
    $query2 = "UPDATE car INNER JOIN rental ON car.ID=rental.carID SET car.status= '2' WHERE car.ID='$id'";
    $result2 = mysqli_query($connection, $query2);
    if (!$result2){
        return "fail";
    }
    //return message
    return "success";
}
//function returns car
function return_car($connection) {
    //data is sanitized
    $id=sanitizeMYSQL($connection, $_POST['return_id']);
    //rental car updated
    $query1 = "UPDATE rental SET status='1', returnDate=CURDATE() WHERE ID='$id'" ;
        $result1 = mysqli_query($connection, $query1);
    if (!$result1){
        return "fail";
    }
    //car table updated
    $query2 = "UPDATE car INNER JOIN rental ON car.ID=rental.carID SET car.status= '1' WHERE rental.ID='$id'";
    $result2 = mysqli_query($connection, $query2);
    if (!$result2){
        return "fail";
    }
    //return message
    return "success";
}
//get rented cars 
    function get_rented($connection) {
     //results will be stored in final array
    $final = array();
    $final["rented_car"] = array();
    //write a query about the enrolled courses for that student. The student ID is from the session
    $query = "select cs.id, c.Picture, c.picture_type, cs.make,yearmade,cs.model,cs.size,c.status, cm.`Name`, r.`ID` as rentalid, r.`rentdate`
                from carspecs cs
                inner join car c on c.`CarSpecsID`=cs.`ID`
                inner join rental r on r.`carID`=c.id
                inner join customer cm on cm.id=r.`CustomerID`
                where cm.id='" . $_SESSION["username"] . "' and r.returndate is null";
    $result = mysqli_query($connection, $query);
    if (!$result)
        return json_encode($array);
    else {
        //if there are results that are stored in an array which will be placed in the final array
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
//function returns rental history
function get_history($connection) {
    //final results array
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
    if (!$result)
        return json_encode($array);
    else {
        //if there are results they are stored in an array which will then be placed in the final array
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
    //session is empty
    $_SESSION = array();
//cookies
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]
        );
    }
// session destroyed
    session_destroy();
}
//function is used to build a string for the sql like comparison
function LIKE($column, $words) {
    $LIKE = "";
    $index = 0;
    foreach ($words as $word) {
        if ($index > 0) //after first paremeter like is placed in string
            $LIKE.=" OR ";

        $LIKE.=" $column LIKE '%$word%' ";
        $index++;
    }
    //string is returned
    return $LIKE;
}
echo $result;
