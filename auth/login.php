<?php

include "../connect.php";
$password = sha1($_POST['password']);
$email = filterRequest("email"); 

getData("users" , "user_email = ? AND  user_password = ?" , array($email , $password)) ; 
