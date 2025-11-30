<?php
require_once "../includes/db.php";
session_start();

// Only reporters
// if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reporter'){
//     header("Location: login.php");
//     exit;
// }

$reporter_id = $_SESSION['user_id'];

// Fetch assignments assigned to this reporter
$stmt = $conn->prepare("
    SELECT * 
    FROM assignments 
    WHERE reporter_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$reporter_id]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
include "../includes/header.php";
?>

<style>
body { background-color: #f5f6fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.sidebar { height: 100vh; position: fixed; top: 0; left: 0; width: 240px; background-color: #1E1E2F; padding-top: 60px; color: #fff; border-right: 1px solid #2c2c3e; }
.sidebar h4 { color: #00BFFF; font-weight: 600; padding-left: 20px; }
.sidebar .nav-link { color: #c0c0c0; padding: 10px 20px; display: block; transition: all 0.2s; }
.sidebar .nav-link:hover { background-color: #2c2c3e; color: #00BFFF; border-radius: 5px; }
.sidebar .nav-link.active { background-color: #00BFFF; color: #fff; border-radius: 5px; }
main { margin-left: 240px; padding: 30px; }
.card { border-radius: 10px; padding: 15px; background-color: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 15px; }
.status { font-weight: 600; padding: 3px 8px; border-radius: 5px; color: #fff; }
.status.pending { background-color: #ff9800; }
.status.in_progress { background-color: #00BFFF; }
.status.completed { background-color: #4caf50; }
</style>

<div class="sidebar d-flex flex-column">
    <h4 class="mb-4 mt-2">Pen Press - Reporter</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="submitted_news.php">Submitted News</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link active" href="assignments.php">Assignments</a>
        </li>
        <!-- <li class="nav-item mb-2">
            <a class="nav-link" href="messages.php">Messages</a>
        </li> -->
        <li class="nav-item mb-2">
            <a class="nav-link" href="all_news.php">All News</a>
        </li>
        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </li>
    </ul>
</div>

<main>
    <h2>My Assignments</h2>
    
    <?php if(empty($assignments)): ?>
        <div class="alert alert-info">You have no assignments yet.</div>
    <?php else: ?>
        <?php foreach($assignments as $a): ?>
            <div class="card">
                <h5><?= htmlspecialchars($a['title']); ?></h5>
                <p><?= nl2br(htmlspecialchars($a['description'])); ?></p>
                <p><strong>Category:</strong> <?= htmlspecialchars($a['category']); ?></p>
                <p><strong>Created At:</strong> <?= date("d M Y, H:i", strtotime($a['created_at'])); ?></p>
                <span class="status <?= $a['status']; ?>"><?= ucfirst($a['status']); ?></span>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
