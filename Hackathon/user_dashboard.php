<?php
// user_dashboard.php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    $title    = trim($_POST['title'] ?? '');
    $content  = trim($_POST['content'] ?? '');
    $anonymous = isset($_POST['anonymous']);

    if ($title === '' || $content === '') {
        $msg = "Title and content are required.";
    } else {
        $user_id = $anonymous ? null : $_SESSION['user_id'];

        $stmt = $conn->prepare("
            INSERT INTO report (user_id, title, content, severity, category)
            VALUES (?, ?, ?, NULL, NULL)
        ");

        // For nullable INT in prepared statement:
        if ($user_id === null) {
            $stmt->bind_param("iss", $user_id, $title, $content); // null is fine here
        } else {
            $stmt->bind_param("iss", $user_id, $title, $content);
        }

        try {
            $stmt->execute();
            $msg = "Report submitted successfully.";
        } catch (mysqli_sql_exception $e) {
            $msg = "Error submitting report: " . $e->getMessage();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard - Tipsy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background:#f5f5f5;
            margin:0;
        }
        .topbar {
            background:#333;
            color:white;
            padding:10px 20px;
            display:flex;
            justify-content:space-between;
        }
        a.logout { color:#fff; text-decoration:none; }
        .container {
            padding:20px;
        }
        .panel {
            background:#fff;
            padding:15px;
            border-radius:8px;
            box-shadow:0 0 5px rgba(0,0,0,0.10);
            max-width:600px;
        }
        label { display:block; margin-top:10px; }
        input[type="text"], textarea {
            width:100%;
            padding:8px;
            margin-top:5px;
        }
        textarea {
            min-height:150px;
            resize:vertical;
        }
        button {
            margin-top:15px;
            padding:8px 15px;
            border:none;
            background:#333;
            color:white;
            cursor:pointer;
        }
        button:hover { background:#555; }
        .msg {
            margin-top:10px;
            color:#333;
            font-size:0.9em;
        }
    </style>
</head>
<body>

<div class="topbar">
    <div>Tipsy User Dashboard</div>
    <div>
        Logged in as: <?= htmlspecialchars($_SESSION['user_email']) ?> |
        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Submit a Report</h2>
    <div class="panel">
        <form method="post" action="user_dashboard.php">
            <label>Title</label>
            <input type="text" name="title" required>

            <label>Details</label>
            <textarea name="content" required></textarea>

            <label>
                <input type="checkbox" name="anonymous">
                Submit anonymously
            </label>

            <button type="submit" name="submit_report">Send Report</button>
        </form>
        <?php if ($msg): ?>
            <div class="msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
