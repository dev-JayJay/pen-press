<?php
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pen Press News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
        }
        .navbar-brand {
            font-weight: 600;
            color: #00BFFF !important;
        }
        .navbar-nav .nav-link {
            color: #c0c0c0 !important;
            transition: all 0.2s;
        }
        .navbar-nav .nav-link:hover {
            color: #00BFFF !important;
        }
        .navbar-dark.bg-dark {
            background-color: #1E1E2F !important;
            border-bottom: 1px solid #2c2c3e;
        }
        @media(max-width:768px){
            .navbar-collapse {
                background-color: #1E1E2F;
            }
        }
    </style>
</head>
<body>
<!-- <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="/users/dashboard.php">Pen Press News</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="/users/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-danger" href="/users/logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/users/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="/users/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav> -->

<!-- Add spacing to avoid overlap with fixed navbar -->
<div style="margin-top: 10px;"></div>
