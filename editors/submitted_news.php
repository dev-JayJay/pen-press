<?php
require_once "../includes/db.php";
session_start();

// Only allow editors
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'editor'){
    header("Location: login.php");
    exit;
}

// Fetch all news submitted by this editor
$stmt = $conn->prepare("SELECT * FROM news WHERE author_id=? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$news_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "../includes/header.php";
?>

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

    /* Cards */
    .card {
        border-radius: 10px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    .card-title {
        font-weight: 600;
        color: #1E1E2F;
    }
    .card-text {
        color: #555;
    }
    .btn-primary {
        background-color: #00BFFF;
        border-color: #00BFFF;
    }
    .btn-primary:hover {
        background-color: #009acd;
        border-color: #009acd;
    }
    .btn-outline-primary {
        border-color: #00BFFF;
        color: #00BFFF;
    }
    .btn-outline-primary:hover {
        background-color: #00BFFF;
        color: #fff;
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
    <h4 class="mb-4 mt-2">Pen Press News - Editor</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="create_news.php">Create News</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link active" href="submitted_news.php">Submitted News</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="messages.php">Messages</a>
        </li>
        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </li>
    </ul>
</div>

<main>
    <h2>Submitted News</h2>

    <?php if(empty($news_list)): ?>
        <p class="text-muted">You haven't submitted any news yet.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach($news_list as $news): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <?php if($news['image_path']): ?>
                            <img src="<?php echo "../" . $news['image_path']; ?>" class="card-img-top" style="height:200px; object-fit:cover; border-top-left-radius:10px; border-top-right-radius:10px;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $news['title']; ?></h5>
                            <p class="text-muted" style="font-size:0.85rem;">Category: <?php echo ucfirst($news['category']); ?></p>
                            <p class="text-muted" style="font-size:0.85rem;">Status: 
                                <?php 
                                    switch($news['status']){
                                        case 'draft': echo 'Draft'; break;
                                        case 'submitted': echo 'Pending Review'; break;
                                        case 'approved': echo 'Approved'; break;
                                        case 'rejected': echo 'Rejected'; break;
                                        case 'published': echo 'Published'; break;
                                    } 
                                ?>
                            </p>
                            <?php if($news['review_comment']): ?>
                                <p class="text-danger" style="font-size:0.85rem;">Comment: <?php echo $news['review_comment']; ?></p>
                            <?php endif; ?>
                            <a href="edit_news.php?id=<?php echo $news['id']; ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include "../includes/footer.php"; ?>
