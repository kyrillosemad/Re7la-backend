<?php

include "../connect.php";

$username = filterRequest("username");
$password = sha1($_POST['password']);
$email = filterRequest("email");
$phone = filterRequest("phone");
$verifycode     = rand(10000 , 99999);

$stmt = $con->prepare("SELECT * FROM users WHERE user_email = ? OR user_phone = ? ");
$stmt->execute(array($email, $phone));
$count = $stmt->rowCount();
if ($count > 0) {
    printFailure("Wrong At Email Or Password");
} else {

    $data = array(
        "user_name" => $username,
        "user_password" =>  $password,
        "user_email" => $email,
        "user_phone" => $phone,
        "user_verifycode" => $verifycode ,
    );
    // sendEmail($email , "Verfiy Code Ecommerce" , "Verfiy Code $verifycode") ; 
    insertData("users" , $data) ; 

}
