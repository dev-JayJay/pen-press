<?php
require_once "../includes/db.php";
session_start();

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'editor_in_chief'){
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = $_POST['user_id'];

    // Approve user
    if (isset($_POST['approve_user'])) {
        $stmt = $conn->prepare("UPDATE users SET status='approved' WHERE id=?");
        $stmt->execute([$user_id]);
        $success = "User approved!";
    }

    // Reject user
    if (isset($_POST['reject_user'])) {
        $stmt = $conn->prepare("UPDATE users SET status='rejected' WHERE id=?");
        $stmt->execute([$user_id]);
        $success = "User rejected!";
    }

    // Change role
    if (isset($_POST['change_role'])) {

        $new_role = $_POST['role'];
        $editor_type = $_POST['editor_type'] ?? null;

        if ($new_role == "editor" && !$editor_type) {
            $error = "Please select editor type for editor role.";
        } else {
            // If role is reporter → remove editor_type
            if ($new_role == "reporter") {
                $editor_type = NULL;
            }

            $stmt = $conn->prepare("UPDATE users SET role=?, editor_type=? WHERE id=?");
            $stmt->execute([$new_role, $editor_type, $user_id]);

            $success = "User role updated successfully!";
        }
    }
}


$users = $conn->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

include "../includes/header.php";
?>

<style>
.user-card {
    width: 320px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    margin: 15px;
    background: #ffffff;
}
.user-card img {
    width: 100%;
    height: 220px;
    object-fit: cover;
}
.user-card-body {
    padding: 15px;
}
.user-card h5 {
    font-weight: 600;
}
.user-info {
    color: #555;
    font-size: 14px;
}
.card-actions {
    padding: 15px;
    border-top: 1px solid #eee;
    background: #fafafa;
}
.card-actions button {
    width: 100%;
    margin-bottom: 8px;
}
.user-grid {
    display: flex;
    flex-wrap: wrap;
}
</style>

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
            <a class="nav-link " href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="assign_task.php">Assign Task</a>
        </li>
        <!-- <li class="nav-item mb-2">
            <a class="nav-link" href="messages.php">Messages</a>
        </li> -->
        <li class="nav-item mb-2">
            <a class="nav-link" href="all_news.php">All News</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link active" href="eic_manage_users.php">Manage Editor / Reporter</a>
        </li>
        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </li>
    </ul>
</div>

<main style="margin-left:240px; padding:30px;">
<h2>User Management</h2>

<?php if($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<?php if($error): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="user-grid">

<?php foreach ($users as $user): ?>
<div class="user-card">

    <!-- Image -->
    <?php if ($user['passport_path']): ?>
        <img src="../<?php echo $user['passport_path']; ?>" alt="User">
    <?php else: ?>
        <img src="../assets/default_user.png" alt="User">
    <?php endif; ?>

    <!-- Body -->
    <div class="user-card-body">
        <h5><?php echo $user['first_name'] . " " . $user['last_name']; ?></h5>

        <p class="user-info">
            <strong>Email:</strong> <?= $user['email'] ?><br>
            <strong>Role:</strong> <?= ucfirst($user['role']) ?><br>
            <strong>Status:</strong> <?= ucfirst($user['status']) ?><br>
            <strong>Editor Type:</strong> <?= $user['editor_type'] ?: '—' ?>
        </p>

        <!-- ROLE CHANGE -->
        <form method="post">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

            <label class="form-label fw-bold">Change Role</label>
            <select name="role" class="form-select mb-2" required>
                <option value="">-- Select Role --</option>
                <option value="editor" <?= $user['role']=='editor' ? 'selected' : '' ?>>Editor</option>
                <option value="reporter" <?= $user['role']=='reporter' ? 'selected' : '' ?>>Reporter</option>
            </select>

            <label class="form-label">Editor Type</label>
            <select name="editor_type" class="form-select mb-2">
                <option value="">-- Select Type --</option>
                <option value="sport" <?= $user['editor_type']=='sport' ? 'selected' : '' ?>>Sport</option>
                <option value="business" <?= $user['editor_type']=='business' ? 'selected' : '' ?>>Business</option>
                <option value="features" <?= $user['editor_type']=='features' ? 'selected' : '' ?>>Features</option>
            </select>

            <button name="change_role" class="btn btn-warning btn-sm">Save Role</button>
        </form>
    </div>

    <!-- Actions -->
    <div class="card-actions">
        <form method="post">
            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

            <!-- Approve/Reject -->
            <?php if ($user['status'] === 'pending'): ?>
                <button name="approve_user" class="btn btn-success btn-sm">Approve</button>
                <button name="reject_user" class="btn btn-danger btn-sm">Reject</button>
            <?php endif; ?>

        </form>
    </div>

</div>
<?php endforeach; ?>

</div>

</main>

<?php include "../includes/footer.php"; ?>
