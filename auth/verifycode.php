<?php 

include "../connect.php" ;

$email  = filterRequest("email") ; 

$verify = filterRequest("verifycode") ; 

$stmt = $con->prepare("SELECT * FROM users WHERE user_email = '$email' AND user_verifycode = '$verify' ") ; 
 
$stmt->execute() ; 

$count = $stmt->rowCount() ; 

if ($count > 0) {
 
    $data = array("user_approve" => "1") ; 

    updateData("users" , $data , "user_email = '$email'");

}else {
    
 printFailure("verifycode not Correct") ; 

}
?>