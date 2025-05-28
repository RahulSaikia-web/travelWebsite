<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Include database configuration
require_once 'config.php';

// Initialize variables
$place = null;
$error = '';
$success = '';

// Get place_id from URL
$place_id = isset($_GET['place_id']) ? intval($_GET['place_id']) : 0;

// Fetch place details
if ($place_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM places WHERE id = ?");
        $stmt->execute([$place_id]);
        $place = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$place) {
            $error = "Place not found.";
        }
    } catch (PDOException $e) {
        $error = "Error fetching place: " . $e->getMessage();
    }
} else {
    $error = "Invalid place ID.";
}

// Handle booking form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $place) {
    $travel_date = $_POST['travel_date'];
    $travelers = intval($_POST['travelers']);

    // Validation
    if (empty($travel_date) || $travelers <= 0) {
        $error = "Please fill in all fields with valid data.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, place_id, travel_date, travelers) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $place_id, $travel_date, $travelers]);
            $success = "Booking successful! You will be redirected to the home page...";
            header("refresh:2;url=home.php");
        } catch (PDOException $e) {
            $error = "Error creating booking: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Your Trip</title>
    <link rel="stylesheet" href="css/booking.css">
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <ul>
            <li><a href="home.php">Home</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="profile.php">Profile</a></li>
            <?php else: ?>
                <li><a href="signup.php">Sign Up</a></li>
            <?php endif; ?>
            <li><a href="bookings.php">Your Bookings</a></li>
            <li><a href="payments.html">Payments</a></li>
        </ul>
    </div>

    <!-- Booking Section -->
    <div class="booking-container">
        <h2>Book Your Trip</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif ($success): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <?php if ($place): ?>
            <div class="place-details">
                <img src="<?php echo htmlspecialchars($place['image']); ?>" alt="<?php echo htmlspecialchars($place['name']); ?>">
                <h3><?php echo htmlspecialchars($place['name']); ?></h3>
                <p class="cost">$<?php echo number_format($place['travel_cost'], 2); ?></p>
                <p class="description"><?php echo htmlspecialchars($place['description']); ?></p>
            </div>

            <form method="POST" action="booking.php?place_id=<?php echo $place_id; ?>">
                <label for="travel_date">Travel Date:</label>
                <input type="date" name="travel_date" required>
                
                <label for="travelers">Number of Travelers:</label>
                <input type="number" name="travelers" min="1" required>
                
                <button type="submit" class="booking-btn">Confirm Booking</button>
            </form>
        <?php else: ?>
            <p>No place selected. <a href="home.php">Return to Home</a></p>
        <?php endif; ?>
    </div>
</body>
</html>