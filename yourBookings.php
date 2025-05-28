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

// Fetch user's bookings
try {
    $stmt = $pdo->prepare("
        SELECT b.id, b.travel_date, b.travelers, b.created_at, p.name AS place_name 
        FROM bookings b 
        JOIN places p ON b.place_id = p.id 
        WHERE b.user_id = ? 
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching bookings: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Bookings</title>
    <link rel="stylesheet" href="css/YouBookings.css">
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

    <!-- Bookings Section -->
    <div class="bookings-container">
        <h2>Your Bookings</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif (empty($bookings)): ?>
            <p class="no-bookings">You have no bookings yet. <a href="home.php">Explore destinations</a></p>
        <?php else: ?>
            <div class="bookings-table">
                <table>
                    <thead>
                        <tr>
                            <th>Place</th>
                            <th>Travel Date</th>
                            <th>Travelers</th>
                            <th>Booked On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['place_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['travel_date']); ?></td>
                                <td><?php echo htmlspecialchars($booking['travelers']); ?></td>
                                <td><?php echo htmlspecialchars($booking['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>