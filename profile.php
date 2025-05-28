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

// Fetch user details
try {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $error = "User not found.";
    }
} catch (PDOException $e) {
    $error = "Error fetching profile: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="css/profile.css">
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
            <li><a href="payments.html">Payments</a></li>
        </ul>
    </div>

    <!-- Profile Section -->
    <div class="profile-container">
        <h2>Your Profile</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif ($user): ?>
            <div class="profile-details">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><a href="logout.php" class="logout-btn">Logout</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>