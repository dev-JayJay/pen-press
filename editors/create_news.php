<?php
require_once "../includes/db.php";
session_start();

// Only allow editors
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'editor'){
    header("Location: login.php");
    exit;
}

// GET ASSIGNMENTS FOR THIS EDITOR
$stmt = $conn->prepare("SELECT * FROM assignments WHERE editor_id = ? AND status = 'pending'");
$stmt->execute([$_SESSION['user_id']]);
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = '';
$error = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $title = trim($_POST['title']);
    $summary = trim($_POST['summary']);
    $body = trim($_POST['body']);
    $category = $_POST['category'];
    $assignment_id = $_POST['assignment_id'];

    // Handle image upload
    $image_path = null;
    if(isset($_FILES['image']) && $_FILES['image']['error'] === 0){
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_path = 'uploads/news/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../' . $image_path);
    }

    if($title && $body && $category && $assignment_id){

        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)) . '-' . uniqid();

        $stmt = $conn->prepare("
            INSERT INTO news (title, slug, summary, body, category, image_path, author_id, assignment_id, status)
            VALUES (?,?,?,?,?,?,?,?, 'submitted')
        ");

        $stmt->execute([
            $title,
            $slug,
            $summary,
            $body,
            $category,
            $image_path,
            $_SESSION['user_id'],
            $assignment_id
        ]);

        $success = "News submitted successfully for review!";
    } else {
        $error = "Please fill in all required fields!";
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

<div class="sidebar d-flex flex-column">
    <h4 class="mb-4 mt-2">Pen Press - EIC</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link active" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="assign_task.php">Assign Task</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="create_editor.php">Create Editor</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="messages.php">Messages</a>
        </li>
        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </li>
    </ul>
</div>

<div class="sidebar d-flex flex-column">
    <h4 class="mb-4 mt-2">Pen Press News - Editor</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link active" href="create_news.php">Create News</a>
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

<main>
    <h2>Create News</h2>

    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm p-4 mb-4">
        <form method="post" enctype="multipart/form-data">
            <label>Assignment</label>
            <select name="assignment_id" class="form-control" required>
                <option value="">Select Assignment</option>
                <?php foreach($assignments as $task): ?>
                    <option value="<?= $task['id']; ?>">
                        <?= htmlspecialchars($task['note']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br>
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" class="form-control" name="title" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Summary</label>
                <textarea class="form-control" name="summary" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Body</label>
                <textarea class="form-control" name="body" rows="6" required></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select class="form-select" name="category" required>
                    <option value="">-- Select Category --</option>
                    <option value="sport" <?php echo $_SESSION['editor_type']=='sport'?'selected':''; ?>>Sport</option>
                    <option value="business" <?php echo $_SESSION['editor_type']=='business'?'selected':''; ?>>Business</option>
                    <option value="features" <?php echo $_SESSION['editor_type']=='features'?'selected':''; ?>>Features</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Image (optional)</label>
                <input type="file" class="form-control" name="image" accept="image/*">
            </div>
            <button class="btn btn-primary">Submit News</button>
        </form>
    </div>
</main>

<?php include "../includes/footer.php"; ?>
