<?php
require_once "../includes/db.php";
session_start();
$message = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Fetch user
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {

        // Readers are allowed regardless of status
        if ($user['role'] !== 'reader' && $user['status'] !== 'approved') {

            // Block all non-readers until approved
            if ($user['status'] === 'pending') {
                $message = "Your account is awaiting approval. Please check back later.";
            } elseif ($user['status'] === 'rejected') {
                $message = "Your account registration was rejected. Contact the admin.";
            } else {
                $message = "Your account is not approved for access.";
            }

        } else {

            // Login user
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];

            // Redirect based on role
            switch($user['role']) {
                case 'editor_in_chief':
                    header("Location: /pen-press/editor_in_chief/dashboard.php");
                    break;
                case 'editor':
                    header("Location: /pen-press/editors/dashboard.php");
                    break;
                case 'reporter':
                    header("Location: /pen-press/reporter/dashboard.php");
                    break;
                case 'reader':
                default:
                    header("Location: dashboard.php");
                    break;
            }
            exit;
        }

    } else {
        $message = "Invalid login credentials.";
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
        max-width: 400px;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        background-color: #ffffff;
    }
    .login-card h2 {
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
        <h2>Login</h2>
        <?php if($message): ?>
            <div class="alert alert-danger"><?php echo $message; ?></div>
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
        <p class="mt-3 text-center">Don't have an account? <a href="register.php">Register here</a></p>
        <p class="mt-3 text-center">
    Want to join Pen Press? <a href="join-penpress.php">Apply here</a>
</p>

    </div>
</div>

<?php include "../includes/footer.php"; ?>
