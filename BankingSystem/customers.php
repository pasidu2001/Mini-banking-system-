<?php
session_start();
require_once 'config.php';

// Check login
if (!isset($_SESSION['UserID'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Handle form submission to add customer
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $dob = $_POST['dob'] ?? null;
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nationalID = trim($_POST['nationalID'] ?? '');

    if (!$firstName || !$lastName) {
        $error = "First name and last name are required.";
    } else {
        // Insert customer using prepared statement
        $stmt = $conn->prepare("INSERT INTO Customers (FirstName, LastName, DateOfBirth, Address, PhoneNumber, Email, NationalID) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $firstName, $lastName, $dob, $address, $phone, $email, $nationalID);

        if ($stmt->execute()) {
            $success = "Customer added successfully!";
        } else {
            $error = "Error adding customer: " . $conn->error;
        }

        $stmt->close();
    }
}

// Fetch all customers
$result = $conn->query("SELECT * FROM Customers ORDER BY CustomerID DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Customers | Banking System</title>
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
    <h1>Manage Customers</h1>
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
        <label for="firstName">First Name *</label>
        <input type="text" name="firstName" id="firstName" required />

        <label for="lastName">Last Name *</label>
        <input type="text" name="lastName" id="lastName" required />

        <label for="dob">Date of Birth</label>
        <input type="date" name="dob" id="dob" />

        <label for="address">Address</label>
        <textarea name="address" id="address" rows="3"></textarea>

        <label for="phone">Phone Number</label>
        <input type="text" name="phone" id="phone" />

        <label for="email">Email</label>
        <input type="email" name="email" id="email" />

        <label for="nationalID">National ID</label>
        <input type="text" name="nationalID" id="nationalID" />

        <input type="submit" value="Add Customer" />
    </form>

    <h2>Customer List</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>DOB</th>
                <th>Address</th>
                <th>Phone</th>
                <th>Email</th>
                <th>National ID</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['CustomerID']) ?></td>
                    <td><?= htmlspecialchars($row['FirstName']) ?></td>
                    <td><?= htmlspecialchars($row['LastName']) ?></td>
                    <td><?= htmlspecialchars($row['DateOfBirth']) ?></td>
                    <td><?= htmlspecialchars($row['Address']) ?></td>
                    <td><?= htmlspecialchars($row['PhoneNumber']) ?></td>
                    <td><?= htmlspecialchars($row['Email']) ?></td>
                    <td><?= htmlspecialchars($row['NationalID']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" style="text-align:center;">No customers found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
