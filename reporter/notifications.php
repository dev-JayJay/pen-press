<?php
require_once "../includes/db.php";
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// FETCH notifications for this user
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notif_id'])){
    $notif_id = (int)$_POST['notif_id'];
    $stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
    $stmt->execute([$notif_id, $_SESSION['user_id']]);
}

$stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$stmt->execute([$_SESSION['user_id']]);
$unread_count = $stmt->fetchColumn();


include "../includes/header.php";
?>

<style>
body {
    background-color: #f5f6fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
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
}
.sidebar .nav-link.active {
    background-color: #00BFFF;
    color: white;
}
main {
    margin-left: 240px;
    padding: 30px;
}
.notification {
    background: #fff;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.notification.unread {
    border-left: 5px solid #00BFFF;
}
.badge {
    font-size: 0.8rem;
    margin-left: 5px;
}
</style>

<div class="sidebar d-flex flex-column">
    <h4 class="mb-4 mt-2">Pen Press - Reporter</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="submitted_news.php">Submitted News</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="assignments.php">Assignments</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link active" href="notifications.php">
                Notifications 
                <?php if($unread_count > 0): ?>
                    <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <!-- <li class="nav-item mb-2">
            <a class="nav-link" href="messages.php">Messages</a>
        </li> -->
        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </li>
    </ul>
</div>

<main>
    <h2>Notifications</h2>

    <?php if(empty($notifications)): ?>
        <p class="text-muted">No notifications yet.</p>
    <?php else: ?>
        <?php foreach($notifications as $n): ?>
    <div class="notification <?php echo $n['is_read'] ? '' : 'unread'; ?>">
        <?php echo htmlspecialchars($n['message']); ?>
        <br>
        <small class="text-muted"><?php echo $n['created_at']; ?></small>

        <?php if(!$n['is_read']): ?>
            <form method="post" style="display:inline;">
                <input type="hidden" name="notif_id" value="<?php echo $n['id']; ?>">
                <button class="btn btn-sm btn-outline-primary">Mark as Read</button>
            </form>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

    <?php endif; ?>
</main>

<?php include "../includes/footer.php"; ?>
