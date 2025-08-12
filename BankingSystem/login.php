<?php
session_start();
require_once 'config.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    } else {
        $passHash = hash('sha256', $password);

        $stmt = $conn->prepare("SELECT UserID, Role FROM Users WHERE Username = ? AND PasswordHash = ?");
        $stmt->bind_param("ss", $username, $passHash);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $_SESSION['UserID'] = $row['UserID'];
            $_SESSION['Username'] = $username;
            $_SESSION['Role'] = $row['Role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid login credentials.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Login | Banking System</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #ccf0f7;
        display: flex;
        height: 100vh;
        align-items: center;
        justify-content: center;
        margin: 0;
        color: #0a3d62;
    }
    .login-container {
        background: #ffffff;
        padding: 40px;
        border-radius: 15px;
        box-shadow: 0 0 15px rgba(10, 61, 98, 0.2);
        width: 100%;
        max-width: 400px;
    }
    h2 {
        margin-bottom: 20px;
        color: #0a3d62;
    }
    label {
        display: block;
        margin: 12px 0 6px;
        font-weight: 600;
        color: #0a3d62;
    }
    input[type="text"], input[type="password"] {
        width: 100%;
        padding: 10px;
        border-radius: 6px;
        border: 1px solid #b0d4de;
        box-sizing: border-box;
        font-size: 16px;
        color: #0a3d62;
        background-color:rgb(0, 0, 0);
    }
    input[type="text"]:focus, input[type="password"]:focus {
        border-color: #ca7feb;
        outline: none;
        background-color: #e8f0f9;
    }
    input[type="submit"] {
        width: 100%;
        padding: 12px;
        margin-top: 20px;
        background-color: #ca7feb;
        border: none;
        border-radius: 6px;
        color: white;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease-in-out;
    }
    input[type="submit"]:hover {
        background-color: #b46ee1;
    }
    .error {
        color: #d32f2f;
        margin-top: 15px;
        font-weight: 600;
    }
    .footer {
        margin-top: 20px;
        text-align: center;
        font-size: 12px;
        color: #6c7a89;
    }
</style>
</head>
<body>
<div class="login-container">
    <h2>Banking System Login</h2>
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required autofocus />

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required />

        <input type="submit" value="Login" />
    </form>
    
</div>
</body>
</html>
