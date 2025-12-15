<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'karaoke_user');
define('DB_PASSWORD', 'karaoke_pass');
define('DB_NAME', 'karaoke');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($conn === false){
    die("ERROR: Could not connect. " . $conn->connect_error);
}
?>