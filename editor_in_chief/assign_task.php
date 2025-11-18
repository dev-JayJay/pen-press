<?php
require_once "../includes/db.php";
session_start();

// Only allow EIC
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'editor_in_chief'){
    header("Location: login.php");
    exit;
}

// Handle form submission
$success = '';
$error = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $editor_id = $_POST['editor_id'];
    $note = $_POST['note'];

    if($editor_id && $note) {
        $stmt = $conn->prepare("INSERT INTO assignments (editor_id, assigned_by, note) VALUES (?, ?, ?)");
        $stmt->execute([$editor_id, $_SESSION['user_id'], $note]);
        $success = "Task assigned successfully!";
    } else {
        $error = "All fields are required.";
    }
}

// Fetch editors for the dropdown
$stmt = $conn->prepare("SELECT * FROM users WHERE role='editor' ORDER BY editor_type, first_name");
$stmt->execute();
$editors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all assignments
$stmt = $conn->prepare("SELECT a.*, u.first_name, u.last_name, u.editor_type 
                        FROM assignments a 
                        JOIN users u ON a.editor_id = u.id 
                        ORDER BY a.created_at DESC");
$stmt->execute();
$assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <h4 class="mb-4 mt-2">Pen Press News - EIC</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link active" href="assign_task.php">Assign Task</a>
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

<!-- Main Content -->
<main>
    <h2>Assign News Task</h2>

    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Task Assignment Form -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label for="editor_id" class="form-label">Select Editor</label>
                    <select class="form-select" name="editor_id" required>
                        <option value="">-- Select Editor --</option>
                        <?php foreach($editors as $editor): ?>
                            <option value="<?php echo $editor['id']; ?>">
                                <?php echo ucfirst($editor['editor_type']) . " - " . $editor['first_name'] . " " . $editor['last_name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="note" class="form-label">Task Note / Instructions</label>
                    <textarea class="form-control" name="note" rows="3" required></textarea>
                </div>
                <button class="btn btn-primary">Assign Task</button>
            </form>
        </div>
    </div>

    <!-- List of Previous Assignments -->
    <h4>All Assignments</h4>
    <div class="row">
        <?php foreach($assignments as $a): ?>
            <div class="col-md-4 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo ucfirst($a['editor_type']) . " Editor: " . $a['first_name'] . " " . $a['last_name']; ?></h5>
                        <p class="card-text"><?php echo $a['note']; ?></p>
                        <p class="text-muted" style="font-size:0.85rem;">Status: <?php echo ucfirst($a['status']); ?></p>
                        <p class="text-muted" style="font-size:0.75rem;">Assigned on: <?php echo $a['created_at']; ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<?php include "../includes/footer.php"; ?>
