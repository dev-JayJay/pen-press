<?php
require_once "../includes/db.php";

$message = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (first_name,last_name,email,password,role) VALUES (?,?,?,?, 'reader')");
    if($stmt->execute([$first_name, $last_name, $email, $password])) {
        $message = "Registration successful. <a href='login.php'>Login here</a>";
    } else {
        $message = "Error occurred. Try again.";
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
        min-height: 80vh;
    }
    .register-card {
        width: 100%;
        max-width: 450px;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        background-color: #ffffff;
    }
    .register-card h2 {
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

<div class="register-container">
    <div class="register-card">
        <h2>Reader Registration</h2>
        <?php if($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" placeholder="Enter your first name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" placeholder="Enter your last name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Enter a password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
        <p class="mt-3 text-center">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
