<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Page</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

    <!-- Sign Up Form -->
    <div class="login-container">
        <h2>Sign Up</h2>
        <?php
        // Include database configuration
        require_once 'config.php';

        // Initialize error message variable
        $error = '';
        $success = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            // Basic validation
            if (empty($email) || empty($password) || empty($confirm_password)) {
                $error = "All fields are required.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format.";
            } elseif ($password !== $confirm_password) {
                $error = "Passwords do not match.";
            } elseif (strlen($password) < 8) {
                $error = "Password must be at least 8 characters long.";
            } else {
                try {
                    // Check if email already exists
                    $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error = "Email already registered.";
                    } else {
                        // Hash the password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                        // Insert user into database
                        $stmt = $pdo->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
                        $stmt->execute([$email, $hashed_password]);
                        $success = "Registration successful! Redirecting to login...";
                        // Redirect to login.php after 2 seconds
                        header("refresh:2;url=login.php");
                    }
                } catch (PDOException $e) {
                    $error = "Error: " . $e->getMessage();
                }
            }
        }
        ?>

        <!-- Display error or success message -->
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php elseif ($success): ?>
            <p style="color: green;"><?php echo $success; ?></p>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <input type="email" name="email" placeholder="Enter your Email" required>
            <input type="password" name="password" placeholder="Enter your Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm your Password" required>
            <button type="submit" class="signup-btn">Sign Up</button>
        </form>
        <a href="login.php"><button class="login-btn">Back to Login</button></a>
    </div>
</body>
</html>