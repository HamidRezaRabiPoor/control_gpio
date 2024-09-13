<?php

    // CONNECT to DATABASE
    $con = mysqli_connect("localhost","username","password","db_name");
    if(mysqli_connect_errno())
        echo "something went wrong". mysqli_connect_error();
    
   


// LOGIN function
function login_to_db($username, $password)
{
    global $con;
    // Query to find records
    $query = mysqli_query($con, "SELECT * FROM login WHERE username='$username' AND password='$password' ");

    // Check to find existed records
    if(mysqli_num_rows($query)> 0)
    {
        
        echo "confirm the process";
    }else{
        echo "no such arguments exist in database";
    }
}



// UPDATE function 
// records are goint to receive from android side.
function board_update($firstLED, $secondLED, $thirdLED, $forthLED)
{
    global $con;
    // Update records in database by mobile app.
    $query = mysqli_query($con, "UPDATE mcu SET `16`='$firstLED', `5`='$secondLED', `4`='$thirdLED', `0`='$forthLED' WHERE node_id = 1");
    // Check the result
    if($query)
    {
        echo "Record got updated successfully";
    }else{
        echo "unable to update records";
    }
}


// BOARD_FEED function
// Records are going to send to nodeMcu side.
function board_feed()
{
    global $con;
    // Query to get records
    $query = mysqli_query($con, "SELECT `16`,`5`,`4`,`0` FROM mcu WHERE node_id = 1");
    if(mysqli_num_rows($query)> 0)
    {

        // Get records as an object
        $row = mysqli_fetch_assoc($query); 

        // Turn records to the json
        return json_encode($row); 
    }else{
        return "No relevan record exists";
    }
}

// Get data from mcu side and insert into database
function sensor_insertion($temperature, $humidity, $gpio16, $gpio5, $gpio4, $gpio0)
{
    $temperature = floatval($temperature);
    $humidity = floatval($humidity);
    
        global $con;
        if($temperature != 0 && $humidity !=0)
        {
         $query = mysqli_query($con, "INSERT INTO sensor(`temperature`, `humidity`) VALUES('$temperature', '$humidity')");
         $second_query = mysqli_query($con, "UPDATE gpio_status SET `gpio_16`='$gpio16', `gpio_5`='$gpio5', `gpio_4`='$gpio4', `gpio_0`='$gpio0'");
        }
        if($query){
            echo "sensor record inserted";
        }else{
            echo "nothing inserted into sensor table";
        }
    
    return ;
 

}

// Get ranges of recorded values by sensor 

function senser_record_selection()
{
    global $con;
    // Define current time 
    $time = time();
    $currentTime = date("Y-m-d", $time);

    // Extract all records related to current time
    $query = mysqli_query($con, "SELECT * FROM sensor WHERE `time`='$currentTime'");

    // Prepare data to parse as jsonArray
    $row = array();
    $row_array = array();
    if(mysqli_num_rows($query) > 0)
    {
    // Put each row into an object
        while($row = mysqli_fetch_assoc($query))
        {
             // Put each object into array
            array_push($row_array, $row);
        }
            echo json_encode($row_array);
    }else{

        echo "no record related to the time existed";
    }
    
}

// Prepare gpio records for android side 
function gpio_reports_for_android()
{
    global $con;
    // Query request
    $query = mysqli_query($con , "SELECT * FROM gpio_status");
    $row = array();
    $row_array = array();
    // Check the result of request
    if(mysqli_num_rows($query) > 0)
    {
        // Turn result into an object
        $row = mysqli_fetch_assoc($query);
        // Fetch to an array
        array_push($row_array, $row);
        // Turn into json
        echo json_encode($row_array);

    }else{
        // Log the error
        echo "unable to catch gpio states";
    }
}

function android_packet($android_packet)
{
    global $con;
    // Query from android
    if($android_packet != null)
    {
        $send_query = mysqli_query($con, "UPDATE connection_test SET android_packet='$android_packet'");
    }
   

}
function android_answer_packet()
{
    global $con;
    $row = array();
    // Query for answer
    $receive_query = mysqli_query($con, "SELECT mcu_packet FROM connection_test");
    // Proper result for android
        $row = mysqli_fetch_assoc($receive_query);
        echo json_encode($row);
    
}

// Test connection: get packet sent by android and give the same as answer
function mcu_packet($mcu_packet)
{
    global $con;
    // Query from android
    $receive_query = mysqli_query($con, "SELECT android_packet FROM connection_test");
    // Query as answer
    if($mcu_packet != null)
    {
        $send_query = mysqli_query($con, "UPDATE connection_test SET mcu_packet='$mcu_packet'");
    }
        $row = array();
    // Proper result for mcu
    if(mysqli_num_rows($receive_query) > 0)
    {
        $row = mysqli_fetch_assoc($receive_query);
        echo json_encode($row);

    }
}

// Function to rectify the table of sensor records
function delete_table_extra_records(){
    global $con;
    // Define the current time
    $time = time();
    $current_time = date("Y-m-d", $time);
    // Define time gap
    $gap = 60 * 60 * 24* 2;
    // Define durration
    $duration = $time - $gap;
    // querry to make changes on table and delete extra records based on time
    $previous_time = date("Y-m-d", $duration);
    $query = mysqli_query($con, "DELETE FROM sensor WHERE NOT `time` BETWEEN '$previous_time' AND '$current_time' ");
}



 //delete_table_extra_records();
 function delete(){
     global $con;
     $query = mysqli_query($con, "DELETE FROM sensor"); 
 }
// delete();








?>
