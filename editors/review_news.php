<?php
require_once "../includes/db.php";
session_start();

// Only allow editors
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'editor'){
    header("Location: login.php");
    exit;
}

$editor_id = $_SESSION['user_id'];
$success = $error = '';

// Fetch the news for review/edit
$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($news_id){
    $stmt = $conn->prepare("
        SELECT n.*, u.first_name AS reporter_first, u.last_name AS reporter_last
        FROM news n
        JOIN users u ON n.author_id = u.id
        WHERE n.id = ?
          AND (n.edited_by = ? OR n.edited_by IS NULL)
    ");
    $stmt->execute([$news_id, $editor_id]);
    $news = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$news){
        die("News not found or not assigned to you for review.");
    }
}

// Handle review/edit submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $news_id = intval($_POST['news_id']);
    $title = trim($_POST['title']);
    $summary = trim($_POST['summary']);
    $body = trim($_POST['body']);
    $category = $_POST['category'];
    $status = $_POST['status']; // approved / rejected / pending
    $review_comment = trim($_POST['review_comment']);

    // Handle image upload
    $image_path = $news['image_path']; // default existing image
    if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_path = 'uploads/news/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image_path);
    }

    if($title && $body && $category){
        $stmt = $conn->prepare("
            UPDATE news SET 
                title = ?, 
                summary = ?, 
                body = ?, 
                category = ?, 
                image_path = ?, 
                eic_status = ?, 
                eic_comment = ?, 
                edited_by = ?, 
                updated_at = NOW()
            WHERE id = ?
        ");
        if($stmt->execute([$title, $summary, $body, $category, $image_path, $status, $review_comment, $editor_id, $news_id])){
            $success = "News review and edits submitted successfully!";
            // Refresh the news data
            $stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
            $stmt->execute([$news_id]);
            $news = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Failed to submit review. Try again!";
        }
    } else {
        $error = "Title, Body, and Category are required!";
    }
}

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

.sidebar .nav-link {
    color: #c0c0c0;
    padding: 10px 20px;
}
.sidebar .nav-link.active {
    background-color: #00BFFF;
    color: #fff;
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
</style>

<div class="sidebar d-flex flex-column">
    <h4 class="mb-4 mt-2">Pen Press News - Editor</h4>

    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="submitted_news.php">Submitted News</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="messages.php">Messages</a>
        </li>
        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </li>
    </ul>
</div>

<main style="margin-left: 240px; padding: 30px;">
    <h2>Review & Edit News</h2>

    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if($news): ?>
    <div class="card shadow-sm p-4 mb-4">
        <!-- <p><strong>Reporter:</strong> <?php echo $news['reporter_first'] . ' ' . $news['reporter_last']; ?></p> -->

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="news_id" value="<?php echo $news['id']; ?>">

            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($news['title']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Summary</label>
                <textarea class="form-control" name="summary" rows="3"><?php echo htmlspecialchars($news['summary']); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Body</label>
                <textarea class="form-control" name="body" rows="6" required><?php echo htmlspecialchars($news['body']); ?></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Category</label>
                <select class="form-select" name="category" required>
                    <option value="sport" <?php echo $news['category']=='sport'?'selected':''; ?>>Sport</option>
                    <option value="business" <?php echo $news['category']=='business'?'selected':''; ?>>Business</option>
                    <option value="features" <?php echo $news['category']=='features'?'selected':''; ?>>Features</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Current Image</label><br>
                <?php if($news['image_path']): ?>
                    <img src="<?php echo "../".$news['image_path']; ?>" style="max-width:200px; margin-bottom:10px;"><br>
                <?php else: ?>
                    <span class="text-muted">No image uploaded</span><br>
                <?php endif; ?>
                <label>Replace Image (optional)</label>
                <input type="file" class="form-control" name="image" accept="image/*">
            </div>

            <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="pending" <?php echo $news['eic_status']=='pending'?'selected':''; ?>>Pending</option>
                    <option value="approved" <?php echo $news['eic_status']=='approved'?'selected':''; ?>>Approved</option>
                    <option value="rejected" <?php echo $news['eic_status']=='rejected'?'selected':''; ?>>Rejected</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Review Comment</label>
                <textarea name="review_comment" class="form-control" rows="3"><?php echo htmlspecialchars($news['eic_comment']); ?></textarea>
            </div>

            <button class="btn btn-primary">Submit Review & Edits</button>
        </form>
    </div>
    <?php else: ?>
        <p class="text-muted">No news selected for review.</p>
    <?php endif; ?>
</main>

<?php include "../includes/footer.php"; ?>
