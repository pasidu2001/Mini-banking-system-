<?php
session_start();
require_once 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Handle new branch form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $branchName = trim($_POST['branchName'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $manager = trim($_POST['manager'] ?? '');

    if (!$branchName) {
        $error = "Branch name is required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO Branches (BranchName, Address, PhoneNumber, ManagerName) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $branchName, $address, $phone, $manager);

        if ($stmt->execute()) {
            $success = "Branch added successfully!";
        } else {
            $error = "Error adding branch: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch all branches
$result = $conn->query("SELECT * FROM Branches ORDER BY BranchName ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Branches | Banking System</title>
<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #ccf0f7;
        color: #0a3d62;
        margin: 0;
        padding: 20px;
    }
    h1 {
        color: #ca7feb;
    }
    a {
        color: #ca7feb;
        text-decoration: none;
        font-weight: bold;
    }
    a:hover {
        text-decoration: underline;
    }
    .container {
        max-width: 900px;
        margin: auto;
        background: #fff;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 0 15px rgba(10, 61, 98, 0.15);
    }
    form label {
        display: block;
        margin: 10px 0 5px;
        font-weight: 600;
    }
    form input, form textarea {
        width: 100%;
        padding: 8px;
        border-radius: 6px;
        border: 1px solid #b0d4de;
        box-sizing: border-box;
        font-size: 16px;
        color: #0a3d62;
        background-color: #f6fbfc;
    }
    form input[type="submit"] {
        margin-top: 15px;
        background-color: #ca7feb;
        color: white;
        font-weight: 700;
        border: none;
        cursor: pointer;
        padding: 12px;
        border-radius: 6px;
        transition: background-color 0.3s ease-in-out;
    }
    form input[type="submit"]:hover {
        background-color: #b46ee1;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
    }
    table th, table td {
        border: 1px solid #b0d4de;
        padding: 10px;
        text-align: left;
    }
    table th {
        background-color: #ca7feb;
        color: white;
    }
    .message {
        margin: 15px 0;
        padding: 10px;
        border-radius: 6px;
    }
    .error {
        background-color: #ffd6d6;
        color: #d32f2f;
        font-weight: 600;
    }
    .success {
        background-color: #d4edda;
        color: #2e7d32;
        font-weight: 600;
    }
    .top-links {
        margin-bottom: 20px;
    }
</style>
</head>
<body>

<div class="container">
    <h1>Manage Branches</h1>
    <div class="top-links">
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>

    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <label for="branchName">Branch Name *</label>
        <input type="text" name="branchName" id="branchName" required />

        <label for="address">Address</label>
        <textarea name="address" id="address" rows="3"></textarea>

        <label for="phone">Phone Number</label>
        <input type="text" name="phone" id="phone" />

        <label for="manager">Manager Name</label>
        <input type="text" name="manager" id="manager" />

        <input type="submit" value="Add Branch" />
    </form>

    <h2>Branches List</h2>
    <table>
        <thead>
            <tr>
                <th>Branch ID</th>
                <th>Branch Name</th>
                <th>Address</th>
                <th>Phone Number</th>
                <th>Manager Name</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['BranchID']) ?></td>
                    <td><?= htmlspecialchars($row['BranchName']) ?></td>
                    <td><?= htmlspecialchars($row['Address']) ?></td>
                    <td><?= htmlspecialchars($row['PhoneNumber']) ?></td>
                    <td><?= htmlspecialchars($row['ManagerName']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" style="text-align:center;">No branches found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
