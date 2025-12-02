<?php
require_once "../includes/db.php";
session_start();

// Only logged in users
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Handle status change or delete (only admin/EIC)
if(isset($_POST['action']) && ($user_role=='admin' || $user_role=='editor_in_chief')){
    $news_id = intval($_POST['news_id']);
    
    if($_POST['action'] === 'delete'){
        $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
        $stmt->execute([$news_id]);
    } elseif($_POST['action'] === 'update_status'){
        $new_status = $_POST['new_status'];
        $stmt = $conn->prepare("UPDATE news SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $news_id]);
    }
    header("Location: all_news.php");
    exit;
}

// Fetch news
$sql = "SELECT n.*, u.first_name AS reporter_first, u.last_name AS reporter_last 
        FROM news n 
        JOIN users u ON n.author_id = u.id";

$params = [];
if($search){
    $sql .= " WHERE n.title LIKE ? OR n.summary LIKE ? OR n.category LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?";
    $like_search = "%$search%";
    $params = [$like_search, $like_search, $like_search, $like_search, $like_search];
}

$sql .= " ORDER BY n.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$news_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$stmt->execute([$_SESSION['user_id']]);
$unread_count = $stmt->fetchColumn();

include "../includes/header.php";
?>

<style>
    body {
    background-color: #f5f6fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
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
    display: block;
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

/* Main content */
main {
    margin-left: 240px;
    padding: 30px;
}

/* Search Bar */
form input.form-control {
    max-width: 320px;
    display: inline-block;
}
form button {
    vertical-align: top;
}

/* Grid for news cards */
.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

/* Cards */
.news-card {
    background: #ffffff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(0,0,0,0.07);
    transition: 0.25s ease;
    display: flex;
    flex-direction: column;
    border: 1px solid #eee;
}

.news-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.12);
}

.news-card img {
    width: 100%;
    height: 170px;
    object-fit: cover;
    background: #ddd;
}

/* Card inner content */
.news-card .content {
    padding: 16px 18px;
}

.news-card h5 {
    margin: 0 0 6px;
    font-size: 1.1rem;
    font-weight: 700;
    color: #222;
}

/* Status badge refined */
.badge-status {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: capitalize;
    color: #fff;
}

.badge-draft { background: #6c757d; }
.badge-submitted { background: #00BFFF; }
.badge-approved { background: #28a745; }
.badge-published { background: #6f42c1; }

/* Meta info */
.news-meta {
    font-size: 0.85rem;
    line-height: 1.35rem;
    color: #444;
    margin-top: 12px;
    border-top: 1px solid #eee;
    padding-top: 12px;
}

/* Action buttons area */
.actions {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 18px 18px;
    margin-top: auto;
}

/* Buttons */
.btn-view {
    background: #00BFFF;
    color: #fff;
    padding: 6px 12px;
    font-size: 0.8rem;
    border-radius: 6px;
    text-decoration: none;
}

.btn-view:hover { background: #0095cc; }

.btn-outline-primary,
.btn-outline-danger {
    font-size: 0.8rem;
    padding: 6px 10px;
    border-radius: 6px;
    border-width: 1px;
}

.btn-outline-primary {
    border: 1px solid #00BFFF;
    color: #00BFFF;
}
.btn-outline-primary:hover {
    background:#00BFFF; color:#fff;
}

.btn-outline-danger {
    border: 1px solid #dc3545;
    color: #dc3545;
}
.btn-outline-danger:hover {
    background:#dc3545; color:#fff;
}

/* Status badges */
.badge-status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    color: #fff;
    text-transform: capitalize;
}
.badge-draft { background: gray; }
.badge-submitted { background: #00BFFF; }
.badge-approved { background: #28a745; }
.badge-published { background: #6f42c1; }

/* Buttons */
.btn-view, .btn-outline-primary, .btn-outline-danger {
    font-size: 0.8rem;
    padding: 4px 8px;
    margin-right: 5px;
    border-radius: 6px;
    text-decoration: none;
}
.btn-view {
    background: #00BFFF;
    color: #fff;
    border: none;
}
.btn-view:hover { background: #009acd; }
.btn-outline-primary {
    border: 1px solid #00BFFF;
    color: #00BFFF;
    background: transparent;
}
.btn-outline-primary:hover {
    background: #00BFFF;
    color: #fff;
}
.btn-outline-danger {
    border: 1px solid #dc3545;
    color: #dc3545;
    background: transparent;
}
.btn-outline-danger:hover {
    background: #dc3545;
    color: #fff;
}

/* Responsive */
@media(max-width: 768px) {
    .sidebar { position: relative; width: 100%; height: auto; }
    main { margin-left: 0; padding: 20px; }
    form input.form-control { width: 100%; margin-bottom: 10px; }
    form button { width: 100%; }
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
            <a class="nav-link" href="notifications.php">
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


<main style="margin-left:240px; padding:30px;">
    <h2>All News</h2>

    <!-- Search Bar -->
    <form method="get" class="mb-3">
        <input type="text" name="search" placeholder="Search news..." value="<?php echo htmlspecialchars($search); ?>" class="form-control" style="max-width:320px; display:inline-block;">
        <button class="btn btn-primary">Search</button>
    </form>

    <?php if(empty($news_list)): ?>
        <p class="text-muted">No news found.</p>
        <?php else: ?>
        
        <div class="news-grid">
            <?php foreach($news_list as $news): ?>
                <div class="news-card">
                    <img src="<?php echo $news['image_path'] ? '../'.$news['image_path'] : '../assets/default.jpg'; ?>">
                    <div class="content">

                        <h5><?php echo htmlspecialchars($news['title']); ?></h5>

                        <span class="badge-status badge-<?php echo $news['status']; ?>">
                            <?php echo $news['status']; ?>
                        </span>

                        <div class="news-meta">
                            <strong>Reporter:</strong> 
                            <?php echo $news['reporter_first'].' '.$news['reporter_last']; ?><br>

                            <strong>Category:</strong> <?php echo $news['category']; ?><br>

                            <small class="text-muted">
                                Created: <?php echo $news['created_at']; ?>
                            </small>
                        </div>

                    </div>
                    <div class="actions">
                        <a href="view_news.php?id=<?php echo $news['id']; ?>" class="btn-view">View</a>

                        <?php if($user_role=='admin' || $user_role=='editor_in_chief'): ?>

                            <form method="post" style="display:inline-block;">
                                <input type="hidden" name="news_id" value="<?php echo $news['id']; ?>">

                                <select name="new_status" class="form-select form-select-sm" style="width:auto; display:inline-block;">
                                    <option value="draft"     <?php echo $news['status']=='draft'?'selected':''; ?>>Draft</option>
                                    <option value="submitted" <?php echo $news['status']=='submitted'?'selected':''; ?>>Submitted</option>
                                    <option value="approved"  <?php echo $news['status']=='approved'?'selected':''; ?>>Approved</option>
                                    <option value="published" <?php echo $news['status']=='published'?'selected':''; ?>>Published</option>
                                </select>

                                <button type="submit" name="action" value="update_status" class="btn-outline-primary">Update</button>
                            </form>

                            <form method="post" style="display:inline-block;" onsubmit="return confirm('Delete this news?');">
                                <input type="hidden" name="news_id" value="<?php echo $news['id']; ?>">
                                <button type="submit" name="action" value="delete" class="btn-outline-danger">Delete</button>
                            </form>

                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
    </div>


    <?php endif; ?>
</main>

<?php include "../includes/footer.php"; ?>
