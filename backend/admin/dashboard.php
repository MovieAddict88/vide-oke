<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

require_once "../includes/db.php";

// Song submission logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_song'])) {
    $title = trim($_POST['title']);
    $artist = trim($_POST['artist']);
    $source_type = trim($_POST['source_type']);
    $video_source = '';

    // Generate a unique song number
    do {
        $song_number = rand(100000, 999999);
        $sql_check = "SELECT id FROM songs WHERE song_number = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $song_number);
        $stmt_check->execute();
        $stmt_check->store_result();
        $is_duplicate = $stmt_check->num_rows > 0;
        $stmt_check->close();
    } while ($is_duplicate);

    if ($source_type === 'upload') {
        if (isset($_FILES["video_file"]) && $_FILES["video_file"]["error"] == 0) {
            $target_dir = "../uploads/";
            $target_file = $target_dir . basename($_FILES["video_file"]["name"]);
            if (move_uploaded_file($_FILES["video_file"]["tmp_name"], $target_file)) {
                $video_source = 'uploads/' . basename($_FILES["video_file"]["name"]);
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    } else {
        $video_source = trim($_POST['video_link']);
    }

    if (!empty($title) && !empty($artist) && !empty($video_source)) {
        $sql = "INSERT INTO songs (song_number, title, artist, source_type, video_source) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssss", $song_number, $title, $artist, $source_type, $video_source);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Fetch songs to display
$songs = [];
$sql = "SELECT id, song_number, title, artist, video_source FROM songs ORDER BY id DESC";
if ($result = $conn->query($sql)) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $songs[] = $row;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .wrapper{ width: 80%; padding: 20px; margin: auto; margin-top: 50px; }
        .welcome-banner { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="d-flex justify-content-between welcome-banner">
            <h2>Admin Dashboard</h2>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </div>

        <h3>Add New Song</h3>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Artist</label>
                <input type="text" name="artist" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Source Type</label>
                <select name="source_type" class="form-control" id="source_type_selector">
                    <option value="link">Link (YouTube, Direct, etc.)</option>
                    <option value="upload">Upload</option>
                </select>
            </div>
            <div class="form-group" id="video_link_group">
                <label>Video Link</label>
                <input type="text" name="video_link" class="form-control">
            </div>
            <div class="form-group" id="video_upload_group" style="display: none;">
                 <label>Upload Video</label>
                <input type="file" name="video_file" class="form-control-file">
            </div>
            <div class="form-group">
                <input type="submit" name="submit_song" class="btn btn-primary" value="Add Song">
            </div>
        </form>

        <hr>

        <h3>Manage Songs</h3>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Song Number</th>
                    <th>Title</th>
                    <th>Artist</th>
                    <th>Source</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($songs as $song): ?>
                <tr>
                    <td><?php echo htmlspecialchars($song['song_number']); ?></td>
                    <td><?php echo htmlspecialchars($song['title']); ?></td>
                    <td><?php echo htmlspecialchars($song['artist']); ?></td>
                    <td><?php echo htmlspecialchars($song['video_source']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.getElementById('source_type_selector').addEventListener('change', function() {
            var linkGroup = document.getElementById('video_link_group');
            var uploadGroup = document.getElementById('video_upload_group');
            if (this.value === 'upload') {
                linkGroup.style.display = 'none';
                uploadGroup.style.display = 'block';
            } else {
                linkGroup.style.display = 'block';
                uploadGroup.style.display = 'none';
            }
        });
    </script>
</body>
</html>