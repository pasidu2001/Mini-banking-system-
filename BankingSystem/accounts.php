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

// Handle new account form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerID = $_POST['customerID'] ?? '';
    $branchID = $_POST['branchID'] ?? '';
    $accountType = trim($_POST['accountType'] ?? '');
    $openDate = $_POST['openDate'] ?? date('Y-m-d');
    $status = $_POST['status'] ?? 'Active';

    if (!$customerID || !$branchID || !$accountType) {
        $error = "Please fill all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO Accounts (CustomerID, BranchID, AccountType, Balance, OpenDate, Status) VALUES (?, ?, ?, 0.00, ?, ?)");
        $stmt->bind_param("iisss", $customerID, $branchID, $accountType, $openDate, $status);

        if ($stmt->execute()) {
            $success = "Account added successfully!";
        } else {
            $error = "Error adding account: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch all accounts with customer and branch names
$sql = "SELECT a.AccountID, c.FirstName, c.LastName, b.BranchName, a.AccountType, a.Balance, a.OpenDate, a.Status
        FROM Accounts a
        JOIN Customers c ON a.CustomerID = c.CustomerID
        JOIN Branches b ON a.BranchID = b.BranchID
        ORDER BY a.AccountID DESC";
$result = $conn->query($sql);

// Fetch customers for dropdown
$customers = $conn->query("SELECT CustomerID, FirstName, LastName FROM Customers ORDER BY FirstName ASC");

// Fetch branches for dropdown
$branches = $conn->query("SELECT BranchID, BranchName FROM Branches ORDER BY BranchName ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Accounts | Banking System</title>
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
        max-width: 1000px;
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
    form input, form select {
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
    <h1>Manage Accounts</h1>
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
        <label for="customerID">Customer *</label>
        <select name="customerID" id="customerID" required>
            <option value="">-- Select Customer --</option>
            <?php while ($cust = $customers->fetch_assoc()): ?>
                <option value="<?= $cust['CustomerID'] ?>">
                    <?= htmlspecialchars($cust['FirstName'] . ' ' . $cust['LastName']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="branchID">Branch *</label>
        <select name="branchID" id="branchID" required>
            <option value="">-- Select Branch --</option>
            <?php while ($branch = $branches->fetch_assoc()): ?>
                <option value="<?= $branch['BranchID'] ?>">
                    <?= htmlspecialchars($branch['BranchName']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="accountType">Account Type *</label>
        <input type="text" name="accountType" id="accountType" placeholder="e.g., Savings, Checking" required />

        <label for="openDate">Open Date</label>
        <input type="date" name="openDate" id="openDate" value="<?= date('Y-m-d') ?>" />

        <label for="status">Status</label>
        <select name="status" id="status">
            <option value="Active" selected>Active</option>
            <option value="Inactive">Inactive</option>
        </select>

        <input type="submit" value="Add Account" />
    </form>

    <h2>Accounts List</h2>
    <table>
        <thead>
            <tr>
                <th>Account ID</th>
                <th>Customer</th>
                <th>Branch</th>
                <th>Type</th>
                <th>Balance</th>
                <th>Open Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['AccountID']) ?></td>
                    <td><?= htmlspecialchars($row['FirstName'] . ' ' . $row['LastName']) ?></td>
                    <td><?= htmlspecialchars($row['BranchName']) ?></td>
                    <td><?= htmlspecialchars($row['AccountType']) ?></td>
                    <td><?= number_format($row['Balance'], 2) ?></td>
                    <td><?= htmlspecialchars($row['OpenDate']) ?></td>
                    <td><?= htmlspecialchars($row['Status']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" style="text-align:center;">No accounts found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
