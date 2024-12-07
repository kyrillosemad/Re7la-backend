<?php
include "../connect.php"; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seat_id = filterRequest('seat_id'); 

    $stmt = $con->prepare("SELECT seat_status FROM seats WHERE seat_id = ?");
    $stmt->execute([$seat_id]);
    $seat = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($seat && $seat['seat_status'] == 1) { 

        $cancel_code = random_int(100000, 999999);

        $updateStmt = $con->prepare(
            "UPDATE seats SET seat_status = 0, owner_id = NULL, reservation_code = NULL, cancel_code = ? WHERE seat_id = ?"
        );
        $updateStmt->execute([$cancel_code, $seat_id]);

        if ($updateStmt->rowCount() > 0) {
            echo json_encode(array(
                "status" => "success", 
                "message" => "Reservation cancelled successfully!", 
                "cancel_code" => $cancel_code
            ));
        } else {
            echo json_encode(array(
                "status" => "failure", 
                "message" => "Failed to cancel the reservation."
            ));
        }
    } else {
        echo json_encode(array(
            "status" => "failure", 
            "message" => "Seat is not reserved or does not exist."
        ));
    }
}
?>
