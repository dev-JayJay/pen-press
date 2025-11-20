<?php
require_once "../includes/db.php";
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reporter'){
    header("Location: login.php");
    exit;
}

$reporter_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT n.*, a.title as assignment_title 
                        FROM news n
                        LEFT JOIN assignments a ON n.assignment_id = a.id
                        WHERE n.author_id = ?
                        ORDER BY n.created_at DESC");
$stmt->execute([$reporter_id]);
$news_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "../includes/header.php";
?>

<!-- reporter/includes/sidebar.php -->
<style>
    body {
        background-color: #f5f6fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Sidebar */
    .sidebar {
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        width: 240px;
        background-color: #1E1E2F;
        padding-top: 60px;
        color: #fff;
        border-right: 1px solid #2c2c3e;
    }

    .sidebar h4 {
        color: #00BFFF;
        font-weight: 600;
        padding-left: 20px;
    }

    .sidebar .nav-link {
        color: #c0c0c0;
        padding: 10px 20px;
        transition: all 0.2s;
    }

    .sidebar .nav-link:hover {
        background-color: #2c2c3e;
        color: #00BFFF;
        border-radius: 5px;
    }

    .sidebar .nav-link.active {
        background-color: #00BFFF;
        color: #fff;
        border-radius: 5px;
    }

    main {
        margin-left: 240px;
        padding: 30px;
    }

    @media(max-width: 768px) {
        .sidebar {
            position: relative;
            width: 100%;
            height: auto;
        }

        main {
            margin-left: 0;
            padding: 20px;
        }
    }
</style>

<div class="sidebar d-flex flex-column">
    <h4 class="mb-4 mt-2">Pen Press - Reporter</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link " href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link active" href="submitted_news.php">Submitted News</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="assignments.php">Assignments</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link " href="messages.php">Messages</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="all_news.php">All News</a>
        </li>
        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </li>
    </ul>
</div>

<main>

    <div class="container mt-4">
        <h2>My Submitted News</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Assignment</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date Submitted</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($news_list as $news): ?>
                    <tr>
                        <td><?= htmlspecialchars($news['title']); ?></td>
                        <td><?= htmlspecialchars($news['assignment_title'] ?? 'N/A'); ?></td>
                        <td><?= ucfirst($news['category']); ?></td>
                        <td>
                            <?php 
                                if($news['status'] === 'submitted') echo "<span class='badge bg-warning'>Submitted</span>";
                                elseif($news['status'] === 'approved') echo "<span class='badge bg-success'>Approved</span>";
                                elseif($news['status'] === 'rejected') echo "<span class='badge bg-danger'>Rejected</span>";
                            ?>
                        </td>
                        <td><?= date('d M Y', strtotime($news['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include "../includes/footer.php"; ?>
