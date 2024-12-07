<?php
include "../connect.php"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seat_ids = json_decode(filterRequest('seat_ids'), true); 
    $owner_id = filterRequest('owner_id'); 
    $ticket_travel_id = filterRequest('ticket_travel_id');
    $ticket_travel_from = filterRequest('ticket_travel_from'); // إضافة مكان الانطلاق
    $ticket_travel_to = filterRequest('ticket_travel_to'); // إضافة مكان الوصول
    $ticket_travel_date = filterRequest('ticket_travel_date'); // إضافة تاريخ السفر
    
    if (!is_array($seat_ids)) { 
        echo json_encode(array(
            "status" => "failure", 
            "message" => "Invalid seat_ids format."
        ));
        exit;
    }

    $reserved_seats = [];
    $failed_seats = [];

    $stmt = $con->prepare("SELECT seat_id, seat_status FROM seats WHERE seat_id = ?");
    $reservation_code = random_int(100000, 999999); 

    foreach ($seat_ids as $seat_id) {
        if (!is_numeric($seat_id)) {
            $failed_seats[] = $seat_id;
            continue;
        }

        $stmt->execute([$seat_id]);
        $seat = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($seat && $seat['seat_status'] == 0) { 
            $updateStmt = $con->prepare(
                "UPDATE seats 
                    SET seat_status = 1, owner_id = ?, reservation_code = ?, cancel_code = NULL 
                    WHERE seat_id = ?"
            );
            $updateStmt->execute([$owner_id, $reservation_code, $seat_id]);

            if ($updateStmt->rowCount() > 0) {
                $reserved_seats[] = array(
                    "seat_id" => $seat_id, 
                    "reservation_code" => $reservation_code 
                );
            } else {
                $failed_seats[] = $seat_id;
            }
        } else {
            $failed_seats[] = $seat_id;
        }
    }

    if (count($reserved_seats) > 0) {
        $ticket_insert_stmt = $con->prepare(
            "INSERT INTO tickets (ticket_owner_id, ticket_reservation_code, ticket_travel_id, ticket_travel_from, ticket_travel_to, ticket_travel_date) 
            VALUES (?, ?, ?, ?, ?, ?)"
        );
        $ticket_insert_stmt->execute([$owner_id, $reservation_code, $ticket_travel_id, $ticket_travel_from, $ticket_travel_to, $ticket_travel_date]);

        if ($ticket_insert_stmt->rowCount() > 0) {
            echo json_encode(array(
                "status" => "success", 
                "reserved_seats" => $reserved_seats,
                "failed_seats" => $failed_seats
            ));
        } else {
            echo json_encode(array(
                "status" => "failure", 
                "message" => "Seats reserved, but failed to create ticket.",
                "failed_seats" => $failed_seats
            ));
        }
    } else {
        echo json_encode(array(
            "status" => "failure", 
            "message" => "No seats were reserved.", 
            "failed_seats" => $failed_seats
        ));
    }
}
?>
