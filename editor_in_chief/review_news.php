<?php
require_once "../includes/db.php";
session_start();

// Only allow EIC
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'editor_in_chief'){
    header("Location: login.php");
    exit;
}

// Get the news ID
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT n.*, u.first_name, u.last_name 
                        FROM news n
                        JOIN users u ON n.author_id = u.id
                        WHERE n.id=?");
$stmt->execute([$news_id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$news){
    die("News not found.");
}

$success = '';
$error = '';


if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $status = $_POST['status'];
    $review_comment = trim($_POST['review_comment']);

    if(in_array($status, ['approved', 'rejected'])){

        $stmt = $conn->prepare("UPDATE news SET status=?, review_comment=? WHERE id=?");
        $stmt->execute([$status, $review_comment, $news_id]);

        // AUTO-COMPLETE ASSIGNMENT (only if approved)
        if ($status === 'approved') {

            // Get assignment ID linked to news
            $stmt = $conn->prepare("SELECT assignment_id FROM news WHERE id=?");
            $stmt->execute([$news_id]);
            $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($assignment && $assignment['assignment_id']) {

                // Mark assignment as completed
                $stmt = $conn->prepare("UPDATE assignments SET status='completed' WHERE id=?");
                $stmt->execute([$assignment['assignment_id']]);
            }

            $success = "News approved! Assignment marked as completed.";
        } else {
            $success = "News rejected successfully.";
        }

        // Refresh news data
        $stmt = $conn->prepare("SELECT n.*, u.first_name, u.last_name 
                                FROM news n
                                JOIN users u ON n.author_id = u.id
                                WHERE n.id=?");
        $stmt->execute([$news_id]);
        $news = $stmt->fetch(PDO::FETCH_ASSOC);

    } else {
        $error = "Invalid status selected.";
    }
}



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


<!-- Sidebar -->
<div class="sidebar d-flex flex-column">
    <h4 class="mb-4 mt-2">Pen Press News - EIC</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="assign_task.php">Assign Task</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="create_editor.php">Create Editor</a>
        </li>
        <!-- <li class="nav-item mb-2">
            <a class="nav-link active" href="messages.php">Messages</a>
        </li> -->
        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </li>
    </ul>
</div>

<main>
    <h2>Review News</h2>

    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <?php if($news['image_path']): ?>
            <img src="<?php echo "../" . $news['image_path']; ?>"  class="card-img-top" style="height:300px; object-fit:cover; border-top-left-radius:10px; border-top-right-radius:10px;">
        <?php endif; ?>
        <div class="card-body">
            <h4 class="card-title"><?php echo $news['title']; ?></h4>
            <p class="text-muted">By <?php echo $news['first_name'].' '.$news['last_name']; ?> | Category: <?php echo ucfirst($news['category']); ?></p>
            <p class="card-text"><strong>Summary:</strong> <?php echo $news['summary']; ?></p>
            <p class="card-text"><strong>Body:</strong> <?php echo nl2br($news['body']); ?></p>
            <?php if($news['review_comment']): ?>
                <p class="text-danger"><strong>Previous Comment:</strong> <?php echo $news['review_comment']; ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm p-4">
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Review Status</label>
                <select class="form-select" name="status" required>
                    <option value="">-- Select Status --</option>
                    <option value="approved" <?php echo $news['status']=='approved'?'selected':''; ?>>Approve</option>
                    <option value="rejected" <?php echo $news['status']=='rejected'?'selected':''; ?>>Reject</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Comment (Optional)</label>
                <textarea class="form-control" name="review_comment" rows="3"><?php echo htmlspecialchars($news['review_comment']); ?></textarea>
            </div>
            <button class="btn btn-primary">Submit Review</button>
        </form>
    </div>
</main>

<?php include "../includes/footer.php"; ?>
