<?php
include "../connect.php"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $travel_id = filterRequest('travel_id'); 

    $seatDeletion = deleteData('seats', "travel_id = $travel_id", false);
    $travelDeletion = deleteData('travels', "travel_id = $travel_id");
    } 
?>
