<?php
include "../connect.php"; 

$owner_id = filterRequest('owner_id'); 

$stmt = $con->prepare("
    SELECT t.*, 
    s.seat_number, s.seat_coach_number,
    r.*
    FROM tickets t
    LEFT JOIN seats s ON t.ticket_reservation_code = s.reservation_code
    LEFT JOIN travels r ON t.ticket_travel_id = r.travel_id
    WHERE t.ticket_owner_id = ?
");

$stmt->execute([$owner_id]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($tickets) {
    $response = array();
    
    foreach ($tickets as $ticket) {
        if (!isset($response[$ticket['ticket_id']])) {
            $response[$ticket['ticket_id']] = array(
                'ticket_id' => $ticket['ticket_id'],
                'ticket_owner_id' => $ticket['ticket_owner_id'],
                'ticket_reservation_code' => $ticket['ticket_reservation_code'],
                'ticket_travel_id' => $ticket['ticket_travel_id'],
                'ticket_travel_from' => $ticket['ticket_travel_from'],
                'ticket_travel_to' => $ticket['ticket_travel_to'],
                'ticket_travel_date' => $ticket['ticket_travel_date'],
                'travel_id' => $ticket['travel_id'],
                'travel_price' => $ticket['travel_price'],
                'seats' => array() 
            );
        }
    
        $response[$ticket['ticket_id']]['seats'][] = array(
            'seat_number' => $ticket['seat_number'],
            'seat_coach_number'=> $ticket['seat_coach_number'],
        );
    }

    echo json_encode(array("status" => "success", "data" => array_values($response)));
} else {
    echo json_encode(array("status" => "failure", "message" => "No tickets found for this user."));
}

$con = null;
?>
