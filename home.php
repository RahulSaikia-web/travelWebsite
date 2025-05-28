<?php
// Start session to check if user is logged in
session_start();

// Include database configuration
require_once 'config.php';

// Fetch places from database
try {
    $stmt = $pdo->query("SELECT * FROM places");
    $places = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching places: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Travel Destinations</title>
    <link rel="stylesheet" href="css/home.css">
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
            <li><a href="yourBookings.php">Your Bookings</a></li>
            <li><a href="payment.php">Payments</a></li>
        </ul>
    </div>

    <!-- Places Section -->
    <div class="places-container">
        <h2>Explore Our Destinations</h2>
        <div class="places-grid">
            <?php if (empty($places)): ?>
                <p>No destinations available at the moment.</p>
            <?php else: ?>
                <?php foreach ($places as $place): ?>
                    <div class="place-card">
                        <img src="<?php echo htmlspecialchars($place['image']); ?>" alt="<?php echo htmlspecialchars($place['name']); ?>">
                        <h3><?php echo htmlspecialchars($place['name']); ?></h3>
                        <p class="cost">$<?php echo number_format($place['travel_cost'], 2); ?></p>
                        <p class="description"><?php echo htmlspecialchars($place['description']); ?></p>
                        <a href="booking.php?place_id=<?php echo $place['id']; ?>"><button class="booking-btn">Book Now</button></a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>