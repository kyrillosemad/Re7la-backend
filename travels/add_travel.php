<?php
include "../connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $travel_from = filterRequest('travel_from');
    $travel_to = filterRequest('travel_to');
    $travel_date = filterRequest('travel_date');
    $travel_price = filterRequest('travel_price');
    $travel_type = filterRequest('travel_type');
    $seats_count = filterRequest('seats_count'); 
    $coaches_count = filterRequest('coaches_count'); 
    
    $travelData = [
        'travel_from' => $travel_from,
        'travel_to' => $travel_to,
        'travel_date' => $travel_date,
        'travel_price' => $travel_price,
        'travel_type' => $travel_type,
        'seats_count' => $seats_count,
        'coaches_count' => $coaches_count,
    ];

    $travelCount = insertData('travels', $travelData, false);

    if ($travelCount > 0) {
        $travel_id = $con->lastInsertId(); 

        for ($c = 1; $c <= $coaches_count; $c++) {
            $coachData = [
                'coach_travel_id' => $travel_id,
                'coach_number' => $c,
            ];
            insertData('coaches', $coachData, false);
            $coach_id = $con->lastInsertId();
            for ($s = 1; $s <= $seats_count; $s++) {
                $seatData = [
                    'travel_id' => $travel_id,
                    'seat_number' => ($s),
                    'seat_coach_id' => $coach_id,
                    'seat_coach_number'=>$c,
                ];
                insertData('seats', $seatData, false); 
            }
        }

        echo json_encode(array("status" => "success", "message" => "Trip, coaches, and seats added successfully!"));
    } else {
        echo json_encode(array("status" => "failure", "message" => "Failed to add trip."));
    }
}
?>
