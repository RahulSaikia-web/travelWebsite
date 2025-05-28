<?php
// Start session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Include database configuration
require_once '../config.php';

// Handle place addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_place'])) {
    $name = trim($_POST['name']);
    $travel_cost = floatval($_POST['travel_cost']);
    $description = trim($_POST['description']);

    // Validate inputs
    if (empty($name) || empty($travel_cost) || empty($description) || !isset($_FILES['image'])) {
        $error = "All fields are required.";
    } elseif ($travel_cost <= 0) {
        $error = "Travel cost must be greater than 0.";
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = "Error uploading image.";
    } else {
        // Handle image upload
        $target_dir = "../images/places/";
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check === false) {
            $error = "File is not an image.";
        } elseif ($_FILES["image"]["size"] > 5000000) { // 5MB limit
            $error = "Image file is too large.";
        } elseif (!in_array($image_file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
            $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        } else {
            // Move uploaded file
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                try {
                    // Insert place into database
                    $stmt = $pdo->prepare("INSERT INTO places (name, image, travel_cost, description) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, "images/places/" . $image_name, $travel_cost, $description]);
                    $success = "Place added successfully.";
                    // Refresh the page to show updated list
                    header("Location: places.php");
                    exit;
                } catch (PDOException $e) {
                    $error = "Error adding place: " . $e->getMessage();
                    // Delete the uploaded image if database insertion fails
                    if (file_exists($target_file)) {
                        unlink($target_file);
                    }
                }
            } else {
                $error = "Failed to upload image.";
            }
        }
    }
}

// Handle place deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_place_id'])) {
    $place_id = intval($_POST['delete_place_id']);

    try {
        // Fetch the place to get the image path
        $stmt = $pdo->prepare("SELECT image FROM places WHERE id = ?");
        $stmt->execute([$place_id]);
        $place = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($place) {
            // Delete the image file
            $image_path = "../" . $place['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }

            // Delete the place (cascades to bookings due to foreign key)
            $stmt = $pdo->prepare("DELETE FROM places WHERE id = ?");
            $stmt->execute([$place_id]);
            $success = "Place deleted successfully.";
        } else {
            $error = "Place not found.";
        }
    } catch (PDOException $e) {
        $error = "Error deleting place: " . $e->getMessage();
    }
}

// Fetch all places
try {
    $stmt = $pdo->query("SELECT * FROM places ORDER BY created_at DESC");
    $places = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching places: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Places</title>
    <link rel="stylesheet" href="home.css">
</head>
<body class="admin-dashboard-page">
    <!-- Admin Navigation Bar -->
    <div class="admin-navbar">
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="users.php">Users</a></li>
            <li><a href="bookings.php">Bookings</a></li>
            <li><a href="places.php">Places</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Places Section -->
    <div class="admin-places-container">
        <h2>Manage Places</h2>
        <button class="add-place-btn" onclick="openModal()">Add New Place</button>

        <!-- Modal for Adding New Place -->
        <div id="addPlaceModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="closeModal()">&times;</span>
                <h2>Add New Place</h2>
                <?php if (isset($error)): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php elseif (isset($success)): ?>
                    <p class="success"><?php echo htmlspecialchars($success); ?></p>
                <?php endif; ?>
                <form method="POST" action="places.php" enctype="multipart/form-data">
                    <input type="hidden" name="add_place" value="1">
                    <label for="name">Place Name:</label>
                    <input type="text" id="name" name="name" required>
                    
                    <label for="image">Image:</label>
                    <input type="file" id="image" name="image" accept="image/*" required>
                    
                    <label for="travel_cost">Travel Cost ($):</label>
                    <input type="number" id="travel_cost" name="travel_cost" step="0.01" min="0" required>
                    
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required></textarea>
                    
                    <button type="submit" class="submit-btn">Add Place</button>
                </form>
            </div>
        </div>

        <?php if (empty($places)): ?>
            <p class="no-places">No places found.</p>
        <?php else: ?>
            <div class="admin-places-table">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Image</th>
                            <th>Travel Cost</th>
                            <th>Description</th>
                            <th>Created On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($places as $place): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($place['name']); ?></td>
                                <td><img src="../<?php echo htmlspecialchars($place['image']); ?>" alt="<?php echo htmlspecialchars($place['name']); ?>" class="place-image"></td>
                                <td>$<?php echo number_format($place['travel_cost'], 2); ?></td>
                                <td><?php echo htmlspecialchars($place['description']); ?></td>
                                <td><?php echo htmlspecialchars($place['created_at']); ?></td>
                                <td>
                                    <form method="POST" action="places.php" onsubmit="return confirm('Are you sure you want to delete this place?');">
                                        <input type="hidden" name="delete_place_id" value="<?php echo $place['id']; ?>">
                                        <button type="submit" class="delete-btn">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- JavaScript to handle modal -->
    <script>
        // Get the modal
        const modal = document.getElementById("addPlaceModal");

        // Function to open the modal
        function openModal() {
            modal.style.display = "block";
        }

        // Function to close the modal
        function closeModal() {
            modal.style.display = "none";
        }

        // Close the modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>