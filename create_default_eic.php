<?php
require_once "includes/db.php"; 

$first_name = "Admin";
$last_name = "Chief";
$email = "eic@penpress.com";
$password_plain = "eic12345";
$role = "editor_in_chief";


$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);


$stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
$stmt->execute([$email]);

if($stmt->rowCount() == 0){
    $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role, created_at) 
                            VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$first_name, $last_name, $email, $password_hashed, $role]);
    echo "Default EIC created successfully!";
} else {
    echo "EIC already exists!";
}
