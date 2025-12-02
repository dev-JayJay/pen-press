<?php
require_once "../includes/db.php";
session_start();

// Only logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// News ID
if (!isset($_GET['id'])) {
    die("Invalid request.");
}
$news_id = intval($_GET['id']);

// Fetch the news
$stmt = $conn->prepare("
    SELECT n.*, 
           u.first_name AS reporter_first, 
           u.last_name AS reporter_last,
           e.first_name AS editor_first,
           e.last_name AS editor_last
    FROM news n
    LEFT JOIN users u ON n.author_id = u.id
    LEFT JOIN users e ON n.edited_by = e.id
    WHERE n.id = ?
");
$stmt->execute([$news_id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    die("News article not found.");
}

// Handle actions: editor review / EIC status change
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Editor submitting review
    if ($user_role == 'editor' && isset($_POST['submit_review'])) {
        $comment = trim($_POST['review_comment']);
        $stmt = $conn->prepare("UPDATE news SET review_comment=?, status='submitted', edited_by=? WHERE id=?");
        $stmt->execute([$comment, $user_id, $news_id]);
        header("Location: view_news.php?id=$news_id");
        exit;
    }

    // EIC approve / reject
    if ($user_role == 'eic' && isset($_POST['eic_action'])) {
        $status = $_POST['eic_action'];
        $comment = trim($_POST['eic_comment']);

        $stmt = $conn->prepare("UPDATE news SET eic_status=?, eic_comment=? WHERE id=?");
        $stmt->execute([$status, $comment, $news_id]);

        // Auto-publish if approved
        if ($status === "approved") {
            $conn->prepare("UPDATE news SET status='published' WHERE id=?")->execute([$news_id]);
        }

        header("Location: view_news.php?id=$news_id");
        exit;
    }

    // Admin delete
    if ($user_role == 'admin' && isset($_POST['delete_news'])) {
        $conn->prepare("DELETE FROM news WHERE id=?")->execute([$news_id]);
        header("Location: all_news.php");
        exit;
    }
}

$stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$stmt->execute([$_SESSION['user_id']]);
$unread_count = $stmt->fetchColumn();

include "../includes/header.php";
?>



<style>
body {
    background-color: #f5f6fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

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
}
.sidebar .nav-link.active {
    background-color: #00BFFF;
    color: white;
}

main {
    margin-left: 240px;
    padding: 30px;
}

.chat-box {
    height: 450px;
    overflow-y: auto;
    padding: 15px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.message {
    margin-bottom: 10px;
    padding: 10px 15px;
    border-radius: 10px;
    max-width: 75%;
}

.message.you {
    background-color: #00BFFF;
    color: white;
    margin-left: auto;
}

.message.they {
    background-color: #e0e0e0;
    color: black;
}

/* Layout */
.news-container {
    margin-left: 260px;
    padding: 30px;
}

.news-box {
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.news-title {
    font-size: 28px;
    font-weight: bold;
    margin-bottom: 15px;
}

.news-meta {
    color: #666;
    font-size: 14px;
}

.news-image {
    width: 100%;
    max-height: 350px;
    object-fit: cover;
    border-radius: 10px;
    margin: 20px 0;
}

.section-header {
    margin-top: 20px;
    font-size: 20px;
    font-weight: bold;
}
    .badge {
    font-size: 0.8rem;
    margin-left: 5px;
}
</style>

<!-- SIDEBAR -->
<div class="sidebar d-flex flex-column">
    <h4 class="mb-4 mt-2">Pen Press - EIC</h4>

    <ul class="nav flex-column">
        <li class="nav-item mb-2"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item mb-2"><a class="nav-link" href="assign_task.php">Assign Task</a></li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="notifications.php">
                Notifications 
                <?php if($unread_count > 0): ?>
                    <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <!-- <li class="nav-item mb-2"><a class="nav-link active" href="messages.php">Messages</a></li> -->
        <li class="nav-item mb-2">
            <a class="nav-link" href="create_editor.php">Create Editor / Reporter</a>
        </li>
        <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="news-container">
    <div class="news-box">

        <h2 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h2>

        <div class="news-meta">
            <strong>Reporter:</strong> 
            <?php echo $news['reporter_first'] . " " . $news['reporter_last']; ?>
            <br>
            <strong>Category:</strong> <?php echo $news['category']; ?>
            <br>
            <strong>Status:</strong> <?php echo $news['status']; ?>
            <br>
            <strong>EIC Status:</strong> <?php echo $news['eic_status']; ?>
            <br>
            <small>Created: <?php echo $news['created_at']; ?></small>
        </div>

        <!-- Image -->
        <?php if ($news['image_path']): ?>
            <img class="news-image" src="<?php echo "../" . $news['image_path']; ?>" alt="">
        <?php endif; ?>

        <h3 class="section-header">Summary</h3>
        <p><?php echo nl2br(htmlspecialchars($news['summary'])); ?></p>

        <h3 class="section-header">Full Story</h3>
        <p><?php echo nl2br(htmlspecialchars($news['body'])); ?></p>

        <!-- EDITOR REVIEW SECTION -->
        <?php if ($user_role == 'editor'): ?>
            <h3 class="section-header">Submit Your Review</h3>

            <form method="post">
                <textarea name="review_comment" class="form-control" rows="4" placeholder="Write your review..."><?php echo $news['review_comment']; ?></textarea>
                <button class="btn btn-primary mt-3" name="submit_review">Submit Review</button>
            </form>
        <?php endif; ?>

        <!-- EIC DECISION PANEL -->
        <?php if ($user_role == 'eic'): ?>
            <h3 class="section-header">Editor-in-Chief Decision</h3>

            <form method="post">
                <textarea name="eic_comment" class="form-control" rows="3" placeholder="EIC comments..."><?php echo $news['eic_comment']; ?></textarea>

                <button class="btn btn-success mt-3" name="eic_action" value="approved">Approve</button>
                <button class="btn btn-danger mt-3" name="eic_action" value="rejected">Reject</button>
            </form>
        <?php endif; ?>

        <!-- ADMIN DELETE -->
        <?php if ($user_role == 'admin'): ?>
            <form method="post" onsubmit="return confirm('Delete this news?');">
                <button name="delete_news" class="btn btn-danger mt-4">Delete News</button>
            </form>
        <?php endif; ?>

    </div>
</div>

<?php include "../includes/footer.php"; ?>
