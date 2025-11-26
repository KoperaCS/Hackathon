<?php
// user_submit_report.php
session_start();
require 'db.php';
require 'gpt_classify.php';   // ⬅➡ GPT classification included

// Check if the user is logged in as a 'user'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    $title     = trim($_POST['title'] ?? '');
    $content   = trim($_POST['content'] ?? '');
    $anonymous = isset($_POST['anonymous']);

    if ($title === '' || $content === '') {
        $msg = "Title and content are required.";
    } else {
        $user_id = $anonymous ? null : $_SESSION['user_id'];

        // ⭐ GPT CLASSIFICATION HERE
        list($severity, $category) = classify_report($title, $content);

        // Prepare SQL insert
        $stmt = $conn->prepare("
            INSERT INTO report (user_id, title, content, severity, category)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("issss", $user_id, $title, $content, $severity, $category);

        try {
            $stmt->execute();
            header("Location: user_submit_report.php?status=success");
            exit;
        } catch (mysqli_sql_exception $e) {
            $msg = "Error submitting report: " . $e->getMessage();
        }

        $stmt->close();
    }
}

// Show success message
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $msg = "Report submitted successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit a Report - Tipsy</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Arial, sans-serif; background:#f5f5f5; margin:0; }
        .topbar { background:#333; color:white; padding:10px 20px; display:flex; justify-content:space-between; }
        a.logout { color:#fff; text-decoration:none; }
        .container { padding:20px; max-width:600px; margin: auto; }
        .panel { background:#fff; padding:15px; border-radius:8px; box-shadow:0 0 5px rgba(0,0,0,0.10); margin-top: 20px; }
        label { display:block; margin-top:10px; }
        input[type="text"], textarea { width:100%; padding:8px; margin-top:5px; box-sizing: border-box; }
        textarea { min-height:150px; resize:vertical; }
        button { margin-top:15px; padding:8px 15px; border:none; background:#333; color:white; cursor:pointer; }
        button:hover { background:#555; }
        .msg { margin-top:10px; padding: 10px; border-radius: 4px; background: #e0f7e0; }
        .back-link { display: block; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="topbar">
    <div>Tipsy Report Submission</div>
    <div>
        <a href="user_dashboard.php" class="logout">Back to Dashboard</a> |
        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>

<div class="container">
    <a href="user_dashboard.php" class="back-link">&leftarrow; Back to Dashboard</a>
    <h2>Submit a Report</h2>
    <div class="panel">
        <form method="post" action="user_submit_report.php">
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
