<?php
require_once "../includes/db.php";
session_start();

// Only reporters
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'reporter'){
    header("Location: ../login.php");
    exit;
}

$reporter_id = $_SESSION['user_id'];
$success = $error = '';

function make_slug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    return trim($slug, '-');
}



// Handle form submission for news creation
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $assignment_id = !empty($_POST['assignment_id']) ? intval($_POST['assignment_id']) : null;
    $title = trim($_POST['title']);
    $summary = trim($_POST['summary']);
    $body = trim($_POST['body']);
    $category = $_POST['category'];
    $edited_by = $_POST['edited_by'];
    $slug = make_slug($title) . '-' . uniqid();

    // Image upload
    $image_path = null;
    if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_path = 'uploads/news/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image_path);
    }

    if($title && $body && $category){
        $stmt = $conn->prepare("INSERT INTO news 
            (title, summary, body, image_path, category, author_id, assignment_id, status, edited_by, slug)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'submitted', ?, ?)");
        $stmt->execute([$title, $summary, $body, $image_path, $category, $reporter_id, $assignment_id, $edited_by, $slug]);

        // Mark assignment as completed
        if($assignment_id){
            $stmt = $conn->prepare("UPDATE assignments SET status='completed' WHERE id=?");
            $stmt->execute([$assignment_id]);
        }

        $success = "News submitted successfully!";
    } else {
        $error = "Please fill all required fields!";
    }
}

// Fetch latest 6 assignments for this reporter
$stmt = $conn->prepare("
    SELECT * 
    FROM assignments 
    WHERE reporter_id = ?
    ORDER BY created_at DESC
    LIMIT 6
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
            <a class="nav-link active" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="submitted_news.php">Submitted News</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="assignments.php">Assignments</a>
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

    <!-- SUBMIT NEWS FORM (NOW AT TOP) -->
    <?php if(isset($_GET['assignment_id']) && $_GET['assignment_id'] > 0): 
        $assignment_id = intval($_GET['assignment_id']);
        $stmt = $conn->prepare("SELECT * FROM assignments WHERE id=? AND reporter_id=?");
        $stmt->execute([$assignment_id, $reporter_id]);
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
        if($assignment):
    ?>
    <h2 class="mb-3">Submit News for: <?= htmlspecialchars($assignment['title']); ?></h2>

    <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="post" enctype="multipart/form-data" class="mb-5 card p-4 shadow-sm">
        <input type="hidden" name="assignment_id" value="<?= $assignment['id']; ?>">

        <div class="mb-3">
            <label>Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Summary</label>
            <textarea name="summary" class="form-control" rows="3"></textarea>
        </div>

        <div class="mb-3">
            <label>Body</label>
            <textarea name="body" class="form-control" rows="6" required></textarea>
        </div>

        <div class="mb-3">
            <label>Category</label>
            <select name="category" class="form-select" required>
                <option value="">--Select Category--</option>
                <option value="sports">Sports</option>
                <option value="business">Business</option>
                <option value="features">Features</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Assign Editor</label>
            <select name="edited_by" class="form-select" required>
                <option value="">-- Select Editor --</option>
                <?php
                $stmt = $conn->query("SELECT id, first_name, last_name, editor_type FROM users WHERE role='editor'");
                $editors = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach($editors as $editor){
                    echo "<option value='{$editor['id']}'>{$editor['editor_type']} - {$editor['first_name']} {$editor['last_name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Image (Optional)</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>

        <button class="btn btn-primary">Submit News</button>
    </form>

    <?php endif; endif; ?>

    <!-- ASSIGNMENTS SECTION -->
    <h2 class="mb-4">My Latest Assignments</h2>

    <?php if(empty($assignments)): ?>
        <div class="alert alert-info">You have no assignments yet.</div>
    <?php else: ?>

    <div class="row">
        <?php foreach($assignments as $a): ?>
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm p-3">

                    <h5><?= htmlspecialchars($a['title']); ?></h5>
                    <p><?= nl2br(htmlspecialchars($a['description'])); ?></p>
                    <p><strong>Category:</strong> <?= htmlspecialchars($a['category']); ?></p>
                    <p><strong>Created At:</strong> <?= date("d M Y, H:i", strtotime($a['created_at'])); ?></p>

                    <span class="status <?= $a['status']; ?>">
                        <?= ucfirst($a['status']); ?>
                    </span>

                    <?php if($a['status'] !== 'completed'): ?>
                        <form method="get" action="">
                            <input type="hidden" name="assignment_id" value="<?= $a['id']; ?>">
                            <button type="submit" class="btn btn-primary mt-2 w-100">Submit News</button>
                        </form>
                    <?php else: ?>
                        <span class="mt-2 d-block text-success">News Submitted</span>
                    <?php endif; ?>

                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>

</main>

<?php include "../includes/footer.php"; ?>
