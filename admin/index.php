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

// Fetch dashboard stats
try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = $stmt->fetchColumn();

    // Total bookings (orders)
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
    $total_bookings = $stmt->fetchColumn();

    // Total places
    $stmt = $pdo->query("SELECT COUNT(*) FROM places");
    $total_places = $stmt->fetchColumn();
} catch (PDOException $e) {
    $error = "Error fetching dashboard stats: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="home.css">
</head>
<body class="admin-dashboard-page">
    <!-- Admin Navigation Bar -->
    <div class="admin-navbar">
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="users.php">Users</a></li>
            <li><a href="book.php">Bookings</a></li>
            <li><a href="places.php">Places</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Dashboard Section -->
    <div class="dashboard-container">
        <h2>Admin Dashboard</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php else: ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p><?php echo htmlspecialchars($total_users); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Bookings</h3>
                    <p><?php echo htmlspecialchars($total_bookings); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Places</h3>
                    <p><?php echo htmlspecialchars($total_places); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>