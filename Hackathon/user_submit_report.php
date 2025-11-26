<?php
// user_submit_report.php
session_start();
require 'db.php';

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
        
        // Ensure $user_id is the correct type for the bind_param. 
        // A null is often treated as 'i' or 's' in mysqli depending on the version/driver.
        // We'll use a string type for the bind to handle the possibility of null gracefully.
        
        $stmt = $conn->prepare("
            INSERT INTO report (user_id, title, content, severity, category)
            VALUES (?, ?, ?, NULL, NULL)
        ");

        // The type for user_id should be 'i' if it's an INT, but handling nulls 
        // requires careful casting or ensuring MySQL column allows NULL.
        // For simplicity and to match the original code:
        $param_user_id = $user_id;

        // Use 'i' for user_id if it's an integer, even if it's null (mysqli handles it)
        $stmt->bind_param("iss", $param_user_id, $title, $content);

        try {
            $stmt->execute();
            // Clear inputs on success by redirecting
            header("Location: user_submit_report.php?status=success");
            exit;
        } catch (mysqli_sql_exception $e) {
            $msg = "Error submitting report: " . $e->getMessage();
        }
        $stmt->close();
    }
}

// Check for status messages on page load (after successful redirect)
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
    <link rel="stylesheet" href="style.css"> <style>
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
            max-width:600px;
            margin: auto;
        }
        .panel {
            background:#fff;
            padding:15px;
            border-radius:8px;
            box-shadow:0 0 5px rgba(0,0,0,0.10);
            margin-top: 20px;
        }
        label { display:block; margin-top:10px; }
        input[type="text"], textarea {
            width:100%;
            padding:8px;
            margin-top:5px;
            box-sizing: border-box;
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
            padding: 10px;
            border-radius: 4px;
            font-size:0.9em;
            color: #333;
            background: #e0f7e0; /* Light green for success */
        }
        .back-link {
            display: block;
            margin-bottom: 20px;
        }
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