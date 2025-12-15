<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "../includes/db.php";

$songs = [];
$sql = "SELECT song_number, title, artist, video_source FROM songs ORDER BY song_number ASC";

if($result = $conn->query($sql)){
    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $songs[] = $row;
        }
    }
}

echo json_encode($songs);

$conn->close();
?>