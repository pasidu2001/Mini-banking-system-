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

// Handle new transaction form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accountID = $_POST['accountID'] ?? '';
    $transactionType = trim($_POST['transactionType'] ?? '');
    $amount = $_POST['amount'] ?? 0;
    $description = trim($_POST['description'] ?? '');
    $transactionDate = $_POST['transactionDate'] ?? date('Y-m-d H:i:s');

    if (!$accountID || !$transactionType || $amount <= 0) {
        $error = "Please fill all required fields with valid values.";
    } else {
        // Start transaction for atomic operation
        $conn->begin_transaction();

        try {
            // Insert transaction
            $stmt = $conn->prepare("INSERT INTO Transactions (AccountID, TransactionDate, TransactionType, Amount, Description) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issds", $accountID, $transactionDate, $transactionType, $amount, $description);
            $stmt->execute();
            $stmt->close();

            // Update account balance
            if (strtolower($transactionType) === 'withdrawal') {
                // Check balance first
                $stmt = $conn->prepare("SELECT Balance FROM Accounts WHERE AccountID = ?");
                $stmt->bind_param("i", $accountID);
                $stmt->execute();
                $stmt->bind_result($balance);
                $stmt->fetch();
                $stmt->close();

                if ($balance < $amount) {
                    throw new Exception("Insufficient balance for withdrawal.");
                }

                $newBalance = $balance - $amount;
            } else {
                // Deposit or others increase balance
                $stmt = $conn->prepare("SELECT Balance FROM Accounts WHERE AccountID = ?");
                $stmt->bind_param("i", $accountID);
                $stmt->execute();
                $stmt->bind_result($balance);
                $stmt->fetch();
                $stmt->close();

                $newBalance = $balance + $amount;
            }

            // Update balance
            $stmt = $conn->prepare("UPDATE Accounts SET Balance = ? WHERE AccountID = ?");
            $stmt->bind_param("di", $newBalance, $accountID);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
            $success = "Transaction added successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Transaction failed: " . $e->getMessage();
        }
    }
}

// Fetch transactions with account info
$sql = "SELECT t.TransactionID, t.TransactionDate, t.TransactionType, t.Amount, t.Description,
               a.AccountID, a.AccountType, c.FirstName, c.LastName
        FROM Transactions t
        JOIN Accounts a ON t.AccountID = a.AccountID
        JOIN Customers c ON a.CustomerID = c.CustomerID
        ORDER BY t.TransactionDate DESC";
$result = $conn->query($sql);

// Fetch accounts for dropdown
$accounts = $conn->query("SELECT a.AccountID, c.FirstName, c.LastName, a.AccountType 
                          FROM Accounts a 
                          JOIN Customers c ON a.CustomerID = c.CustomerID
                          ORDER BY a.AccountID ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Manage Transactions | Banking System</title>
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
        max-width: 1100px;
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
    form input, form select, form textarea {
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
        vertical-align: middle;
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
    <h1>Manage Transactions</h1>
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
        <label for="accountID">Account *</label>
        <select name="accountID" id="accountID" required>
            <option value="">-- Select Account --</option>
            <?php while ($acc = $accounts->fetch_assoc()): ?>
                <option value="<?= $acc['AccountID'] ?>">
                    <?= htmlspecialchars($acc['AccountID'] . " - " . $acc['FirstName'] . " " . $acc['LastName'] . " (" . $acc['AccountType'] . ")") ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="transactionType">Transaction Type *</label>
        <select name="transactionType" id="transactionType" required>
            <option value="">-- Select Type --</option>
            <option value="Deposit">Deposit</option>
            <option value="Withdrawal">Withdrawal</option>
            <option value="Transfer">Transfer</option>
            <option value="Payment">Payment</option>
            <!-- Add more types as needed -->
        </select>

        <label for="amount">Amount *</label>
        <input type="number" step="0.01" min="0.01" name="amount" id="amount" required />

        <label for="transactionDate">Transaction Date</label>
        <input type="datetime-local" name="transactionDate" id="transactionDate" value="<?= date('Y-m-d\TH:i') ?>" />

        <label for="description">Description</label>
        <textarea name="description" id="description" rows="3"></textarea>

        <input type="submit" value="Add Transaction" />
    </form>

    <h2>Transaction List</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date & Time</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Description</th>
                <th>Account ID</th>
                <th>Customer</th>
                <th>Account Type</th>
            </tr>
        </thead>
        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['TransactionID']) ?></td>
                    <td><?= htmlspecialchars($row['TransactionDate']) ?></td>
                    <td><?= htmlspecialchars($row['TransactionType']) ?></td>
                    <td><?= number_format($row['Amount'], 2) ?></td>
                    <td><?= htmlspecialchars($row['Description']) ?></td>
                    <td><?= htmlspecialchars($row['AccountID']) ?></td>
                    <td><?= htmlspecialchars($row['FirstName'] . " " . $row['LastName']) ?></td>
                    <td><?= htmlspecialchars($row['AccountType']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8" style="text-align:center;">No transactions found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
