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

// Handle user update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'], $_POST['email'], $_POST['role'])) {
    $user_id = intval($_POST['user_id']);
    $email = trim($_POST['email']);
    $role = $_POST['role'] === 'admin' ? 'admin' : 'user';

    try {
        $stmt = $pdo->prepare("UPDATE users SET email = ?, role = ? WHERE id = ?");
        $stmt->execute([$email, $role, $user_id]);
        $success = "User updated successfully.";
    } catch (PDOException $e) {
        $error = "Error updating user: " . $e->getMessage();
    }
}

// Fetch all users
try {
    $stmt = $pdo->query("SELECT id, email, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching users: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
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

    <!-- Users Section -->
    <div class="users-container">
        <h2>Manage Users</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif (isset($success)): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if (empty($users)): ?>
            <p class="no-users">No users found.</p>
        <?php else: ?>
            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined On</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <form method="POST" action="users.php">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <td><input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required></td>
                                    <td>
                                        <select name="role">
                                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                    <td><button type="submit" class="update-btn">Update</button></td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>