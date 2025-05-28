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

// Handle payment status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['booking_id'], $_POST['status'])) {
    $booking_id = intval($_POST['booking_id']);
    $status = $_POST['status'] === 'Paid' ? 'Paid' : 'Pending';

    try {
        $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $booking_id]);
        $success = "Payment status updated successfully.";
    } catch (PDOException $e) {
        $error = "Error updating payment status: " . $e->getMessage();
    }
}

// Fetch all bookings
try {
    $stmt = $pdo->prepare("
        SELECT b.id, b.travel_date, b.travelers, b.status, b.created_at, p.name AS place_name, u.email 
        FROM bookings b 
        JOIN places p ON b.place_id = p.id 
        JOIN users u ON b.user_id = u.id 
        ORDER BY b.created_at DESC
    ");
    $stmt->execute();
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
    <title>Manage Bookings</title>
    <link rel="stylesheet" href="home.css">
</head>
<body class="admin-dashboard-page">
    <!-- Admin Navigation Bar -->
    <div class="admin-navbar">
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li><a href="users.php">Users</a></li>
            <li><a href="book.php">Bookings</a></li>
            <li><a href="places.php">Places</a></li>
            <li><a href="../logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Bookings Section -->
    <div class="admin-bookings-container">
        <h2>Manage Bookings</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif (isset($success)): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if (empty($bookings)): ?>
            <p class="no-bookings">No bookings found.</p>
        <?php else: ?>
            <div class="admin-bookings-table">
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Place</th>
                            <th>Travel Date</th>
                            <th>Travelers</th>
                            <th>Booked On</th>
                            <th>Payment Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['email']); ?></td>
                                <td><?php echo htmlspecialchars($booking['place_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['travel_date']); ?></td>
                                <td><?php echo htmlspecialchars($booking['travelers']); ?></td>
                                <td><?php echo htmlspecialchars($booking['created_at']); ?></td>
                                <td class="status-<?php echo strtolower($booking['status']); ?>">
                                    <?php echo htmlspecialchars($booking['status']); ?>
                                </td>
                                <td>
                                    <form method="POST" action="book.php">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <select name="status">
                                            <option value="Pending" <?php echo $booking['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Paid" <?php echo $booking['status'] === 'Paid' ? 'selected' : ''; ?>>Paid</option>
                                        </select>
                                        <button type="submit" class="update-btn">Update</button>
                                    </form>
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