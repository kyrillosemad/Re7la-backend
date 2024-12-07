<?php
include "../connect.php"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner_id_input = $_POST['owner_id'];  

    $stmt = $con->prepare("
        SELECT DISTINCT travels.travel_id
        FROM travels 
        LEFT JOIN seats ON travels.travel_id = seats.travel_id 
        WHERE seats.owner_id = :owner_id
    ");
    $stmt->bindParam(':owner_id', $owner_id_input);
    $stmt->execute();
    
    $travel_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($travel_ids)) {
        $travel_ids_string = implode(',', $travel_ids);
        
        $stmt = $con->prepare("
            SELECT 
                travels.*, 
                seats.*, 
                coaches.coach_id,
                coaches.coach_number,
                stations.station_name,
                stations.station_arrival_date
            FROM travels 
            LEFT JOIN seats ON travels.travel_id = seats.travel_id 
            LEFT JOIN coaches ON travels.travel_id = coaches.coach_travel_id
            LEFT JOIN stations ON travels.travel_id = stations.station_travel_id
            WHERE travels.travel_id IN ($travel_ids_string)
        ");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($data)) {
            $travels = [];

            foreach ($data as $row) {
                $travel_id = $row['travel_id'];
                $coach_id = $row['coach_id'];

                if (!isset($travels[$travel_id])) {
                    $travels[$travel_id] = [
                        'travel_id' => $row['travel_id'],
                        'travel_from' => $row['travel_from'],
                        'travel_to' => $row['travel_to'],
                        'travel_type' => $row['travel_type'],
                        'seats_count' => $row['seats_count'],
                        'coaches_count' => $row['coaches_count'],
                        'travel_date' => $row['travel_date'],
                        'travel_price' => $row['travel_price'],
                        'travel_complete' => $row['travel_complete'],
                        'coaches' => [],
                        'stations' => [] // إضافة محطات الوصول هنا
                    ];
                }

                if ($coach_id && !isset($travels[$travel_id]['coaches'][$coach_id])) {
                    $travels[$travel_id]['coaches'][$coach_id] = [
                        'coach_id' => $coach_id,
                        'coach_number' => $row['coach_number'],
                        'seats' => []
                    ];
                }

                if ($coach_id) {
                    $seat_data = [
                        'seat_id' => $row['seat_id'],
                        'seat_number' => $row['seat_number'],
                        'owner_id' => $row['owner_id'],
                        'travel_id' => $row['travel_id'], 
                        'seat_status' => $row['seat_status']
                    ];

                    if ($row['owner_id'] == $owner_id_input) {
                        $seat_data['reservation_code'] = $row['reservation_code'];
                        $seat_data['cancel_code'] = $row['cancel_code'];
                    }

                    if ($row['seat_coach_id'] == $coach_id) {
                        $travels[$travel_id]['coaches'][$coach_id]['seats'][] = $seat_data;
                    }
                }

                // إضافة المحطات إذا كانت جديدة
                $station_exists = false;
                foreach ($travels[$travel_id]['stations'] as $existing_station) {
                    if ($existing_station['station_name'] == $row['station_name'] && $existing_station['station_arrival_date'] == $row['station_arrival_date']) {
                        $station_exists = true;
                        break;
                    }
                }

                if (!$station_exists && !empty($row['station_name']) && !empty($row['station_arrival_date'])) {
                    $station_data = [
                        'station_name' => $row['station_name'],
                        'station_arrival_date' => $row['station_arrival_date']
                    ];
                    $travels[$travel_id]['stations'][] = $station_data;
                }
            }

            // إعادة ترتيب المحطات والمقاعد
            foreach ($travels as &$travel) {
                $travel['coaches'] = array_values($travel['coaches']);
                $travel['stations'] = array_values($travel['stations']);
            }

            echo json_encode(array("status" => "success", "data" => array_values($travels)));
        } else {
            echo json_encode(array("status" => "failure", "message" => "No trips found."));
        }
    } else {
        echo json_encode(array("status" => "failure", "message" => "No trips found for this owner."));
    }
} else {
    echo json_encode(array("status" => "failure", "message" => "Invalid request method."));
}
?>
