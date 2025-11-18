<?php
require_once "includes/db.php";
session_start();

// Fetch latest approved news (limit 3)
$stmt = $conn->prepare("SELECT n.*, u.first_name, u.last_name FROM news n 
                        JOIN users u ON n.author_id=u.id 
                        WHERE n.status='approved' 
                        ORDER BY n.updated_at DESC LIMIT 3");
$stmt->execute();
$featured_news = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "includes/header.php";
?>

<div class="text-center mb-5">
    <h1>Welcome to Pen Press News</h1>
    <p class="lead">Stay updated with the latest Sports, Business, and Feature news!</p>
    <?php if(!isset($_SESSION['user_id'])): ?>
        <a href="users/register.php" class="btn btn-primary btn-lg me-2">Register</a>
        <a href="users/login.php" class="btn btn-outline-primary btn-lg">Login</a>
    <?php else: ?>
        <a href="users/dashboard.php" class="btn btn-primary btn-lg">Go to Dashboard</a>
    <?php endif; ?>
</div>

<hr>

<h2 class="mb-4">Latest News</h2>
<div class="row">
<?php foreach($featured_news as $news): ?>
  <div class="col-md-4 mb-4">
    <div class="card h-100 shadow-sm">
      <?php if($news['image_path']): ?>
        <img src="<?php echo "./" . $news['image_path']; ?>" class="card-img-top" style="height:200px; object-fit:cover;">
      <?php endif; ?>
      <div class="card-body">
        <h5 class="card-title"><?php echo htmlspecialchars($news['title']); ?></h5>
        <p class="card-text"><?php echo substr(htmlspecialchars($news['summary']),0,100).'...'; ?></p>
        <p class="text-muted">By <?php echo htmlspecialchars($news['first_name'].' '.$news['last_name']); ?></p>
        <a href="users/news_detail.php?id=<?php echo $news['id']; ?>" class="btn btn-primary btn-sm">Read More</a>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<hr>

<div class="text-center my-5">
    <h4>Explore Categories</h4>
    <a href="users/category.php?cat=sport" class="btn btn-outline-success me-2">Sports</a>
    <a href="users/category.php?cat=business" class="btn btn-outline-warning me-2">Business</a>
    <a href="users/category.php?cat=features" class="btn btn-outline-info">Features</a>
</div>

<?php include "includes/footer.php"; ?>
