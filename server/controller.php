<?php
include 'connection.php';
session_start(); //start the session
$result = "";
$request_type="search";
//only process the data if there a request was made and the session is active
if (isset($_POST["type"])) {
    session_regenerate_id(); //regenerate the session to prevent fixation
    $_SESSION['start'] = time(); //reset the session start time
    $request_type=$_POST["type"];

    switch ($request_type) { //check the request type
        case "search":
            $result = search_cars($connection);
            break;
        case "rented":
            $result = get_rented($connection);
            break;
        case "history":
            $result = get_history($connection);
            break;
        case "rent":
            $result=drop($connection,$_POST['rent_id']);
            break;
        case "return":
            $result=drop($connection,$_POST['return_id']);
            break;
    }
}
    function search_cars($connection) {


    $final = array();
    $final["search"] = array();
    $query = "SELECT * FROM carspecs";
    $result = mysqli_query($connection, $query);
    if ($result)
        return json_encode($array);
    else {
        $row_count = mysqli_num_rows($result);
        for ($i = 0; $i < $row_count; $i++) {
            $row = mysqli_fetch_array($result);
            $array["picture"] = 'data:' . $row["Picture_Type"] . ';base64,' . base64_encode($row["Picture"]);
            $array["make"] = $row["FirstName"] . " " . $row["LastName"];
            $array["model"] = $row["Gender"] == 'M' ? "Male" : "Female";
            $array["year"] = date_diff(date_create($row["DateOfBirth"]), date_create('now'))->y;
            $array["color"] = $row["color"];
            $array["size"] = $row["size"];
            $array["id"] = $row["rentalid"];
            $final["search"][] = $array;
        }
    }
    return json_encode($final);
}

function rent_car($connection,$course_id) {
	$date= date_create('Y-m-d');
    $id=$_POST['id'];
    $query1 = "UPDATE rental SET status= '2', rentDate='$date' WHERE status='1' AND carID='$id'";
        $result1 = mysqli_query($connection, $query1);
    if (!$result1){
        return "fail";
    }
    $query2 = "UPDATE car SET status= '2' WHERE status='1' AND ID='$id'";
    $result2 = mysqli_query($connection, $query2);
    if (!$result2){
        return "fail";
    }
    return "success";
}

function return_car($connection,$course_id) {
    $date= date_create('Y-m-d');
    $id = $_POST['return_id'];
    $query1 = "UPDATE rental SET status='1', returnDate='$date' WHERE status='2' AND ID='$id'";
    $result1 = mysqli_query($connection, $query1);
    if (!$result1){
        return "fail";
    }
    $query2= "UPDATE car INNER JOIN rental ON car.ID=rental.carID SET car.status='1' WHERE rental.ID='$id'";
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
    $query = "select cs.id, cs.make,yearmade,cs.model,cs.size,c.status, cm.`Name`, r.`ID` as rentalid, r.`rentdate`
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
    $query = "select cs.id, cs.make,yearmade,cs.model,cs.size,c.status,  r.`ID` as rentalid, r.`returndate`
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
            $array["year"] = $row["yearmade"];
            $array["size"] = $row["size"];
            $array["rental_ID"] = $row["rentalid"];
            $array["rent_date"] = $row["returndate"];
            $final["returned_car"][] = $array;
        }
    }
    return json_encode($final);
}

echo $result;
