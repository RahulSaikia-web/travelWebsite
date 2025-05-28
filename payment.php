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

// Fetch user's bookings with payment details
try {
    $stmt = $pdo->prepare("
        SELECT b.id, b.travelers, b.status, p.name AS place_name, p.travel_cost 
        FROM bookings b 
        JOIN places p ON b.place_id = p.id 
        WHERE b.user_id = ? 
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching payment details: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Status</title>
    <link rel="stylesheet" href="css/payment.css">
</head>
<body class="payment-page">
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

    <!-- Payments Section -->
    <div class="payments-container">
        <h2>Your Payment Status</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif (empty($bookings)): ?>
            <p class="no-payments">You have no bookings to display payments for. <a href="home.php">Explore destinations</a></p>
        <?php else: ?>
            <div class="payments-table">
                <table>
                    <thead>
                        <tr>
                            <th>Booking Name</th>
                            <th>Payment Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['place_name']); ?></td>
                                <td>$<?php echo number_format($booking['travel_cost'] * $booking['travelers'], 2); ?></td>
                                <td class="status-<?php echo strtolower($booking['status']); ?>">
                                    <?php echo htmlspecialchars($booking['status']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>