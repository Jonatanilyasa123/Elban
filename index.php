<?php
// Your existing PHP code
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "newvoii";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = intval($_POST['category_id']);
    $position = $_POST['position'];
    $file_type = '';

    if (isset($_FILES['media_file'])) {
        $file = $_FILES['media_file'];
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($file["name"]);
        $uploadOk = 1;
        $file_extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (in_array($file_extension, ['jpg', 'png', 'jpeg', 'gif', 'mp4', 'avi', 'mov'])) {
            $file_type = in_array($file_extension, ['jpg', 'png', 'jpeg', 'gif']) ? 'image' : 'video';
        } else {
            echo "<div class='alert alert-danger'>Sorry, only JPG, JPEG, PNG, GIF, MP4, AVI & MOV files are allowed.</div>";
            $uploadOk = 0;
        }

        if ($uploadOk == 1) {
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                $sql = "DELETE FROM media WHERE category_id = ? AND position = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('is', $category_id, $position);
                $stmt->execute();
                $stmt->close();

                $sql = "INSERT INTO media (file_name, file_type, category_id, position) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssis', $file["name"], $file_type, $category_id, $position);
                if ($stmt->execute()) {
                    echo "<div class='alert alert-success'>The file ". htmlspecialchars(basename($file["name"])). " has been uploaded.</div>";
                } else {
                    echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
                }
                $stmt->close();
            } else {
                echo "<div class='alert alert-danger'>Sorry, there was an error uploading your file.</div>";
            }
        }
    }
}

// Fetch the latest file for each position
$positions = ['full_screen', 'left_bottom', 'right_bottom'];
$files = [];

foreach ($positions as $position) {
    $sql = "SELECT file_name, file_type FROM media WHERE position = ? ORDER BY id DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $position);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $files[$position] = $result->fetch_assoc();
    } else {
        $files[$position] = null;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AP2 - DISPLAY</title>
    <link rel="icon" href="https://www.angkasapura2.co.id/html/favicon.ico" type="image/icon" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <style>
        #upload-section {
            display: none;
        }
    </style>
</head>
<body>
    <header class="text-white text-center py-4 header-bg" id="header">
        <a href="#">
            <img src="asset/logo-AP-full.png" alt="header img" class="img-fluid" />
        </a>
        <h1 class="fw-bold">BANDAR UDARA RADIN INTEN II LAMPUNG</h1>
    </header>

    <main>
        <div id="upload-section" class="container mt-4">
            <h2 class="mb-4">Upload Media File</h2>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="category_id">Category:</label>
                    <select name="category_id" id="category_id" class="form-control" required>
                        <?php
                        $conn = new mysqli($servername, $username, $password, $dbname);
                        $sql = "SELECT * FROM categories";
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            $categories = $result->fetch_all(MYSQLI_ASSOC);
                            foreach ($categories as $category) {
                                echo "<option value='".$category['id']."'>".$category['name']."</option>";
                            }
                        } else {
                            echo "<option value=''>No categories available</option>";
                        }
                        $conn->close();
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="position">Position:</label>
                    <select name="position" id="position" class="form-control" required>
                        <option value="top">Top</option>
                        <option value="left_bottom">Left Bottom</option>
                        <option value="right_bottom">Right Bottom</option>
                        <option value="full_screen">Full Screen</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="media_file">Select file to upload:</label>
                    <input type="file" name="media_file" id="media_file" class="form-control-file" required>
                </div>
                <button type="submit" class="btn btn-primary">Upload File</button>
            </form>
        </div>

        <div class="video-container mt-4">
            <?php
            if (isset($files['full_screen']) && $files['full_screen']) {
                $videoPath = 'uploads/' . $files['full_screen']['file_name'];
                if (file_exists($videoPath)) {
                    echo "<video id='video' class='w-100' controls autoplay>
                            <source src='$videoPath' type='video/mp4' />
                            Your browser does not support the video tag.
                          </video>";
                } else {
                    echo "<p>Video file does not exist: $videoPath</p>";
                }
            } else {
                echo "<p>No video found for full_screen position.</p>";
                echo "<video id='video' class='w-100' controls autoplay>
                        <source src='asset/default-video.mp4' type='video/mp4' />
                        Your browser does not support the video tag.
                      </video>";
            }
            ?>
        </div>

        <div class="scroll-right mt-4">
            <marquee behavior="" direction="left" class="fw-bold">
                Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla eum quia error placeat recusandae hic, nostrum doloremque nihil ducimus repellendus ea eius architecto doloribus sed.
            </marquee>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 col-content mb-4 mb-md-0">
                <?php
                if (isset($files['left_bottom']) && $files['left_bottom']) {
                    $imagePath = 'uploads/' . $files['left_bottom']['file_name'];
                    if (file_exists($imagePath)) {
                        echo "<img id='left-bottom-img' src='$imagePath' class='img-fluid' />";
                    } else {
                        echo "<p>Image file does not exist: $imagePath</p>";
                    }
                } else {
                    echo "<p>No image found for left_bottom position.</p>";
                }
                ?>
            </div>
            <div class="col-md-6 col-content">
                <?php
                if (isset($files['right_bottom']) && $files['right_bottom']) {
                    $imagePath = 'uploads/' . $files['right_bottom']['file_name'];
                    if (file_exists($imagePath)) {
                        echo "<img id='right-bottom-img' src='$imagePath' class='img-fluid' />";
                    } else {
                        echo "<p>Image file does not exist: $imagePath</p>";
                    }
                } else {
                    echo "<p>No image found for right_bottom position.</p>";
                }
                ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let lastUpdate = {
            full_screen: null,
            left_bottom: null,
            right_bottom: null
        };

        function fetchLatestData() {
            fetch('get_latest_data.php')
                .then(response => response.json())
                .then(data => {
                    let updated = false;
                    for (let position in data.latestUpdate) {
                        if (data.latestUpdate[position] !== lastUpdate[position]) {
                            updated = true;
                            lastUpdate[position] = data.latestUpdate[position];
                        }
                    }
                    if (updated) {
                        updateContent(data.files);
                    }
                });
        }

        function updateContent(files) {
            if (files.full_screen) {
                document.getElementById('video').src = 'uploads/' + files.full_screen.file_name;
            }

            if (files.left_bottom) {
                document.getElementById('left-bottom-img').src = 'uploads/' + files.left_bottom.file_name;
            }

            if (files.right_bottom) {
                document.getElementById('right-bottom-img').src = 'uploads/' + files.right_bottom.file_name;
            }
        }

        setInterval(fetchLatestData, 5000); // Polling every 5 seconds
    </script>
</body>
</html>
