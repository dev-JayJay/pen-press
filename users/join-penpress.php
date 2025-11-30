<?php
require_once "../includes/db.php";
session_start();

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $admission_no = trim($_POST['admission_no']);
    $faculty = trim($_POST['faculty']);
    $department = trim($_POST['department']);
    $level = trim($_POST['level']);

    // Default registration values
    $role = 'reporter';
    $status = 'pending';

    // Passport upload
    $passport_path = null;
    if(isset($_FILES['passport']) && $_FILES['passport']['error'] === 0){
        $ext = pathinfo($_FILES['passport']['name'], PATHINFO_EXTENSION);
        $passport_path = 'uploads/passports/' . uniqid() . '.' . $ext;
        move_uploaded_file($_FILES['passport']['tmp_name'], '../' . $passport_path);
    }

    // Validation
    if(!$first_name || !$last_name || !$email || !$password){
        $error = "Please fill all required fields.";
    }

    if(!$error){
        $stmt = $conn->prepare("INSERT INTO users 
            (first_name,last_name,email,password,role,status,editor_type,admission_no,faculty,department,level,passport_path) 
            VALUES (?, ?, ?, ?, ?, ?, NULL, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $first_name, $last_name, $email,
            password_hash($password, PASSWORD_DEFAULT),
            $role, $status,
            $admission_no, $faculty, $department, $level,
            $passport_path
        ]);

        $success = "Registration successful! Please wait for admin approval.";
    }
}

include "../includes/header.php";
?>

<style>
    body {
        background-color: #f5f6fa;
    }
    .register-container {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px 0;
    }
    .register-card {
        width: 100%;
        max-width: 650px;
        padding: 35px;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .register-card h2 {
        font-weight: 600;
        color: #1E1E2F;
        text-align: center;
        margin-bottom: 25px;
    }
    .btn-primary {
        background-color: #00BFFF;
        border-color: #00BFFF;
    }
    .btn-primary:hover {
        background-color: #009acd;
        border-color: #009acd;
    }
    .alert {
        font-size: 0.9rem;
    }
</style>

<div class="register-container">
    <div class="register-card">
        <h2>Create an Account</h2>

        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First Name *</label>
                    <input type="text" class="form-control" name="first_name" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Name *</label>
                    <input type="text" class="form-control" name="last_name" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email *</label>
                <input type="email" class="form-control" name="email" placeholder="example@gmail.com" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password *</label>
                <input type="password" class="form-control" name="password" placeholder="Choose a strong password" required>
            </div>

            <hr>

            <h5 class="mt-3 mb-3">Academic Details (Optional)</h5>

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
                <label class="form-label">Passport Photo</label>
                <input type="file" class="form-control" name="passport" accept="image/*">
            </div>

            <button class="btn btn-primary w-100 mt-2">Register</button>
        </form>

        <p class="mt-3 text-center">
            Already have an account?
            <a href="login.php">Login here</a>
        </p>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
