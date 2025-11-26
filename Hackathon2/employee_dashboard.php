<?php
// user_dashboard.php
session_start();
require 'db.php';

if (!isset($_SESSION['role'])) {
    // 1. Not logged in at all: send to login
    header("Location: login.php");
    exit;
} elseif ($_SESSION['role'] === 'admin') {
    // 2. Wrong role: send to their correct dashboard
    header("Location: admin_dashboard.php");
    exit;
}

$reports = [];
$error_msg = '';

// SQL Query to fetch ALL reports
// We use LEFT JOIN to optionally include the user's email if the report is NOT anonymous.
$sql = "
    SELECT 
        r.report_id, 
        r.title, 
        r.content, 
        r.severity, 
        r.category, 
        r.user_id,
        u.user_email  -- Get the user's email
    FROM report r
    LEFT JOIN user u ON r.user_id = u.user_id
    ORDER BY r.report_id DESC;
";

$result = $conn->query($sql);

if ($result) {
    // Fetch all reports into the $reports array
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
} else {
    $error_msg = "Database error: " . $conn->error;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Tipsy</title>
    <style>
        .topbar a.logout { 
            color:#fff; 
            text-decoration:none; 
            margin-left: 10px; /* Space between email and logout */
        }
        h2 { 
            color: #dc3545; 
            margin-bottom: 20px; 
            text-align: center; /* Center the main title */
            margin-top: 20px; /* Spacing above main title */
        }
        h3 { 
            color: #555; 
            border-bottom: 2px solid #ccc; 
            padding-bottom: 5px; 
            margin-top: 30px; 
        }

        /* Submission Panel Styles */
        .submit-panel {
            text-align: center; /* Centers the text and the button */
            margin-bottom: 20px; /* Space below the panel */
            /* No need for separate background/padding/shadow, it inherits from .panel */
        }
        .submit-panel p {
            margin-bottom: 15px;
        }
        .submit-button {
            padding: 12px 25px;
            font-size: 1.1em;
            border: none;
            background: #007bff; /* Blue for call to action */
            color: white;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block; /* Allows button to be centered by text-align */
            transition: background 0.3s ease;
        }
        .submit-button:hover {
            background: #0056b3;
        }
        
        /* Report Table Styles */
        .reports-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
        }
        .reports-table th, .reports-table td { 
            padding: 12px 15px; 
            text-align: left; 
            border-bottom: 1px solid #ddd; 
        }
        .reports-table th { 
            background-color: #f8d7da; 
            color: #721c24; 
            font-weight: bold; 
        }
        .reports-table tr:hover { 
            background-color: #f9f9f9; 
        }

        /* Severity Colors */
        .severity-CRITICAL { background-color: #f8d7da; color: #721c24; font-weight: bold; }
        .severity-HIGH { color: #dc3545; font-weight: bold; }
        .severity-MEDIUM { color: #ffc107; font-weight: bold; }
        .severity-LOW { color: #17a2b8; }
        
        .anonymous { font-style: italic; color: #6c757d; }
        
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
            max-width:800px; /* Adjusted for better layout */
            margin: auto;
        }
        .panel {
            background:#fff;
            padding:25px; /* Increased padding */
            border-radius:8px;
            box-shadow:0 0 10px rgba(0,0,0,0.15); /* Slightly stronger shadow */
            margin-top: 20px;
            text-align: center; /* Center content in the panel */
        }
        /* Style for the new button */
        .submit-button {
            padding: 12px 25px;
            font-size: 1.1em;
            border: none;
            background: #28a745; /* Green for a call to action */
            color: white;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none; /* Important for <a> tag */
            display: inline-block; /* Allows padding and margin */
            margin: 10px 0;
            transition: background 0.3s ease;
        }
        .submit-button:hover {
            background: #218838;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        
    </style>
</head>
<body>

<div class="topbar">
    <div>Tipsy Employee Dashboard</div>
    <div>
        Logged in as: <?= htmlspecialchars($_SESSION['user_email']) ?> |
        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>

    <div class="panel">
        <p>If you need to report an incident or issue, please use the button below. You will be directed to the submission form.</p>

        <a href="user_submit_report.php" class="submit-button">
            Submit a New Report
        </a>
    </div>

<div class="container">
    <h2>All Submitted Reports</h2>
    
    <div class="panel">
        <?php if (!empty($error_msg)): ?>
            <p style="color: red; font-weight: bold;"><?= htmlspecialchars($error_msg) ?></p>
        <?php elseif (empty($reports)): ?>
            <p>There are currently no reports submitted in the system.</p>
        <?php else: ?>
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Submitted By</th>
                        <th>Category</th>
                        <th>Severity</th>
                        <th>Content Preview</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): 
                        $submitter = $report['user_email'] ? htmlspecialchars($report['user_email']) : '<span class="anonymous">Anonymous</span>';
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($report['report_id']) ?></td>
                            <td><?= htmlspecialchars($report['title']) ?></td>
                            <td><?= $submitter ?></td>
                            <td><?= htmlspecialchars($report['category'] ?? 'N/A') ?></td>
                            <td class="severity-<?= htmlspecialchars(strtoupper($report['severity'] ?? 'N/A')) ?>">
                                <?= htmlspecialchars($report['severity'] ?? 'N/A') ?>
                            </td>
                            <td><?= htmlspecialchars(substr($report['content'], 0, 50)) . (strlen($report['content']) > 50 ? '...' : '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>