<?php
require_once "../includes/db.php";
session_start();

// Only allow EIC
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'editor_in_chief'){
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $editor_type = $_POST['editor_type'];
    $admission_no = trim($_POST['admission_no']);
    $faculty = trim($_POST['faculty']);
    $department = trim($_POST['department']);
    $level = trim($_POST['level']);
    
    // Handle passport upload
    $passport_path = null;
    if(isset($_FILES['passport']) && $_FILES['passport']['error'] === 0){
        $ext = pathinfo($_FILES['passport']['name'], PATHINFO_EXTENSION);
        $passport_path = 'uploads/passports/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['passport']['tmp_name'], '../' . $passport_path);
    }


$role = $_POST['role'] ?? '';
$editor_type_value = ($role === 'editor') ? ($_POST['editor_type'] ?? null) : null;

if(!$first_name || !$last_name || !$email || !$password || !$role){
    $error = "Please fill all required fields!";
}

if($role === 'editor' && !$editor_type_value){
    $error = "Please select an editor type.";
}

// Only insert if no error
if(!$error){
    $stmt = $conn->prepare("INSERT INTO users 
        (first_name,last_name,email,password,role,editor_type,admission_no,faculty,department,level,passport_path) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $first_name,
        $last_name,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        $role,
        $editor_type_value, 
        $admission_no,
        $faculty,
        $department,
        $level,
        $passport_path
    ]);

    $success = ucfirst($role) . " account created successfully!";
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
            <a class="nav-link" href="messages.php">Messages</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="all_news.php">All News</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="create_editor.php">Create Editor / Reporter</a>
        </li>
        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </li>
    </ul>
</div>


<main>
    <h2>Create New Editor</h2>

    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm p-4 mb-4">
        <form method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" name="first_name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" name="last_name" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select class="form-select" name="role" required>
                    <option value="">-- Select Role --</option>
                    <option value="editor" <?php if(isset($_POST['role']) && $_POST['role']=='editor') echo 'selected'; ?>>Editor</option>
                    <option value="reporter" <?php if(isset($_POST['role']) && $_POST['role']=='reporter') echo 'selected'; ?>>Reporter</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Editor Type</label>
                <select class="form-select" name="editor_type">
                    <option value="">-- Select Type --</option>
                    <option value="sport">Sport</option>
                    <option value="business">Business</option>
                    <option value="features">Features</option>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Admission No</label>
                    <input type="text" class="form-control" name="admission_no">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Faculty</label>
                    <input type="text" class="form-control" name="faculty">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Department</label>
                    <input type="text" class="form-control" name="department">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Level</label>
                    <input type="text" class="form-control" name="level">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Passport</label>
                <input type="file" class="form-control" name="passport" accept="image/*">
            </div>

            <button class="btn btn-primary">Create Editor</button>
        </form>
    </div>
</main>

<script>
const roleSelect = document.querySelector('select[name="role"]');
const editorTypeDiv = document.querySelector('select[name="editor_type"]').parentElement;

roleSelect.addEventListener('change', () => {
    if(roleSelect.value === 'editor'){
        editorTypeDiv.style.display = 'block';
    } else {
        editorTypeDiv.style.display = 'none';
    }
});

// Initial check
if(roleSelect.value !== 'editor'){
    editorTypeDiv.style.display = 'none';
}
</script>

<?php include "../includes/footer.php"; ?>
