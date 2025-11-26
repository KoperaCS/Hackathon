<?php
// admin_dashboard.php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$create_msg = '';

// Handle new user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $new_email = trim($_POST['new_email'] ?? '');
    $new_pass  = trim($_POST['new_password'] ?? '');

    if ($new_email === '' || $new_pass === '') {
        $create_msg = "Email and password are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO user (user_email, user_pass) VALUES (?, ?)");
        $stmt->bind_param("ss", $new_email, $new_pass);
        try {
            $stmt->execute();
            $create_msg = "User created successfully.";
        } catch (mysqli_sql_exception $e) {
            $create_msg = "Error creating user: " . $e->getMessage();
        }
        $stmt->close();
    }
}

// Fetch all reports (join user for email if not anonymous)
$sql = "
    SELECT r.report_id, r.title, r.content, r.severity, r.category,
           r.user_id, u.user_email
    FROM report r
    LEFT JOIN user u ON r.user_id = u.user_id
    ORDER BY r.report_id DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Tipsy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
        }
        .topbar {
            background: #333;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
        }
        .container {
            padding: 20px;
        }
        h2 {
            margin-top: 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            background: #fff;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
        }
        th {
            background: #eee;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 5px;
            font-size: 0.8em;
            color: #fff;
        }
        .low    { background: #4caf50; }
        .medium { background: #ff9800; }
        .high   { background: #f44336; }
        .panel {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.10);
            max-width: 400px;
        }
        label { display:block; margin-top:10px; }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 8px;
        }
        button {
            margin-top: 10px;
            padding: 8px 15px;
            border: none;
            background: #333;
            color: white;
            cursor: pointer;
        }
        button:hover { background:#555; }
        .msg {
            margin-top: 10px;
            color: #333;
            font-size: 0.9em;
        }
        a.logout {
            color: #fff;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="topbar">
    <div>Tipsy Admin Dashboard</div>
    <div>
        Logged in as: <?= htmlspecialchars($_SESSION['admin_email']) ?> |
        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Reports</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Reporter</th>
            <th>Title</th>
            <th>Content</th>
            <th>Severity</th>
            <th>Category</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
                $sev = strtolower($row['severity'] ?? '');
                $sev_class = $sev === 'high' ? 'high' : ($sev === 'medium' ? 'medium' : 'low');
            ?>
            <tr>
                <td><?= $row['report_id'] ?></td>
                <td><?= $row['user_id'] ? htmlspecialchars($row['user_email']) : 'Anonymous' ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                <td>
                    <?php if ($row['severity']): ?>
                        <span class="badge <?= $sev_class ?>"><?= htmlspecialchars($row['severity']) ?></span>
                    <?php else: ?>
                        <span>-</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($row['category'] ?? '-') ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Create User</h2>
    <div class="panel">
        <form method="post" action="admin_dashboard.php">
            <label>New User Email</label>
            <input type="email" name="new_email" required>

            <label>New User Password</label>
            <input type="password" name="new_password" required>

            <button type="submit" name="create_user">Create User</button>
        </form>
        <?php if ($create_msg): ?>
            <div class="msg"><?= htmlspecialchars($create_msg) ?></div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
