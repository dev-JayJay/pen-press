<?php
require_once "../includes/db.php";
session_start();

// Only allow editors
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'editor'){
    header("Location: login.php");
    exit;
}

// Fetch assignments assigned to this editor
$editor_id = $_SESSION['user_id'];

// News pending review (submitted by reporters)
$stmt = $conn->prepare("
    SELECT n.*, u.first_name AS reporter_first, u.last_name AS reporter_last
    FROM news n
    JOIN users u ON n.author_id = u.id
    WHERE (n.edited_by = ? OR n.edited_by IS NULL)
      AND n.status = 'submitted'
    ORDER BY n.created_at DESC
    LIMIT 6
");
$stmt->execute([$editor_id]);
$pending_news = $stmt->fetchAll(PDO::FETCH_ASSOC);

// News already reviewed by this editor
$stmt = $conn->prepare("
    SELECT n.*, u.first_name AS reporter_first, u.last_name AS reporter_last
    FROM news n
    JOIN users u ON n.author_id = u.id
    WHERE n.edited_by = ?
    ORDER BY n.updated_at DESC
");
$stmt->execute([$editor_id]);
$reviewed_news = $stmt->fetchAll(PDO::FETCH_ASSOC);


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

main {
    margin-left: 240px;
    padding: 30px;
}

/* Section headings */
main h4 {
    margin-top: 40px;
    color: #1E1E2F;
    font-weight: 600;
}

/* Grid layout for cards */
.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

/* Card styling */
.news-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    flex-direction: column;
}

.news-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}

.news-card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
}

.news-card .card-body {
    padding: 15px 20px;
    flex-grow: 1;
}

.news-card h5 {
    font-weight: 600;
    margin-bottom: 8px;
    font-size: 1.1rem;
}

.news-card p {
    margin: 4px 0;
    font-size: 0.85rem;
    color: #555;
}

/* Status badges */
.badge-status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 6px;
    color: #fff;
    font-size: 12px;
    text-transform: capitalize;
}

.badge-draft { background: gray; }
.badge-submitted { background: #00BFFF; }
.badge-approved { background: #28a745; }
.badge-published { background: #6f42c1; }

/* Buttons */
.btn-review {
    background: #00BFFF;
    color: #fff;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.85rem;
    cursor: pointer;
    transition: 0.2s;
    text-decoration: none;
    display: inline-block;
    margin-top: 10px;
}
.btn-review:hover {
    background: #009acd;
}
</style>



<div class="sidebar d-flex flex-column">
    <h4 class="mb-4 mt-2">Pen Press News - Editor</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link active" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="submitted_news.php">Submitted News</a>
        </li>
        <!-- <li class="nav-item mb-2">
            <a class="nav-link" href="messages.php">Messages</a> -->
        </li>
        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </li>
    </ul>
</div>

<!-- Main Content -->
<main>
    <h2>Welcome, <?php echo $_SESSION['first_name']; ?>!</h2>


    <h4 class="mt-4">Pending Review</h4>
    <?php if(empty($pending_news)): ?>
        <p class="text-muted">No news pending your review.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach($pending_news as $news): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <?php if($news['image_path']): ?>
                            <img src="<?php echo "../" . $news['image_path']; ?>" class="card-img-top" style="height:200px; object-fit:cover; border-top-left-radius:10px; border-top-right-radius:10px;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $news['title']; ?></h5>
                            <p class="text-muted" style="font-size:0.85rem;">Reporter: <?php echo $news['reporter_first'] . ' ' . $news['reporter_last']; ?></p>
                            <p class="text-muted" style="font-size:0.85rem;">Status: <?php echo ucfirst($news['status']); ?></p>
                            <a href="review_news.php?id=<?php echo $news['id']; ?>" class="btn btn-outline-primary btn-sm">Review</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h4 class="mt-4">Reviewed News</h4>
    <?php if(empty($reviewed_news)): ?>
        <p class="text-muted">You haven’t reviewed any news yet.</p>
    <?php else: ?>
        <div class="row">
            <?php foreach($reviewed_news as $news): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm">
                        <?php if($news['image_path']): ?>
                            <img src="<?php echo "../" . $news['image_path']; ?>" class="card-img-top" style="height:200px; object-fit:cover; border-top-left-radius:10px; border-top-right-radius:10px;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $news['title']; ?></h5>
                            <p class="text-muted" style="font-size:0.85rem;">Reporter: <?php echo $news['reporter_first'] . ' ' . $news['reporter_last']; ?></p>
                            <p class="text-muted" style="font-size:0.85rem;">Status: <?php echo ucfirst($news['status']); ?></p>
                            <!-- <a href="review_news.php?id=<?php echo $news['id']; ?>" class="btn btn-outline-primary btn-sm">View</a> -->
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php include "../includes/footer.php"; ?>
