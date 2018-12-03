<?php
include 'connection.php';
//session_start(); //start the session
$result = "";
$request_type="search";
//only process the data if there a request was made and the session is active
if (isset($_POST)) {
    session_regenerate_id(); //regenerate the session to prevent fixation
    $_SESSION['start'] = time(); //reset the session start time

    switch ($request_type) { //check the request type
        case "search":
            $result = search_cars($connection);
            break;
        case "rented":
            $result = get_rented($connection);
            break;
        case "logout":
            //logout();
            //$result= "success";
            break;
    }
    
}
    function search_cars($connection) {
    $array = array();
    $query = "SELECT * FROM carspecs";
    $result = mysqli_query($connection, $query);
    return json_encode($array);
    if ($result)
        return json_encode($array);
    else {
        $row_count = mysqli_num_rows($result);
        if ($row_count == 1) { //if the student exists in the database
            $row = mysqli_fetch_array($result);
            $array["Picture"] = 'data:' . $row["Picture_Type"] . ';base64,' . base64_encode($row["Picture"]);
            $array["Name"] = $row["FirstName"] . " " . $row["LastName"];
            $array["Gender"] = $row["Gender"] == 'M' ? "Male" : "Female";
            $array["Age"] = date_diff(date_create($row["DateOfBirth"]), date_create('now'))->y;
        }
    }
    return json_encode($array);
}

    function get_rented($connection) {
    $final = array();
    $final["rentals"] = array();
    //write a query about the enrolled courses for that student. The student ID is from the session
    $query = "SELECT rental_ID from rental where CustomerID='" . $_SESSION["username"] . "'";
    $result = mysqli_query($connection, $query);
    $text = "";
    if (!$result)
        return json_encode($array);
    else {
        $row_count = mysqli_num_rows($result);
        for ($i = 0; $i < $row_count; $i++) {
            $row = mysqli_fetch_array($result);
            $array = array();
            $array["rental_ID"] = $row["ID"];    
            $final["rentals"][] = $array;
        }
    }
    return json_encode($final);
}

echo $result;
