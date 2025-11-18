<?php
require_once "../includes/db.php";
session_start();

if(isset($_SESSION['user_id']) && $_SESSION['role'] === 'editor_in_chief'){
    header("Location: dashboard.php");
    exit;
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND role='editor_in_chief'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($password, $user['password'])){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}

include "../includes/header.php";
?>

<style>
    body {
        background-color: #f5f6fa;
    }
    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
    }
    .login-card {
        width: 100%;
        max-width: 380px;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        background-color: #ffffff;
    }
    .login-card h3 {
        color: #1E1E2F;
        font-weight: 600;
        margin-bottom: 20px;
        text-align: center;
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

<div class="login-container">
    <div class="login-card">
        <h3>Editor In Chief Login</h3>
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
