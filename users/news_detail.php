<?php
require_once "../includes/db.php";
session_start();

if (!isset($_GET['id'])) {
    die("Invalid article ID.");
}

$news_id = intval($_GET['id']);

// Fetch article
$stmt = $conn->prepare("SELECT n.*, u.first_name, u.last_name 
                        FROM news n 
                        JOIN users u ON n.author_id = u.id
                        WHERE n.id=? AND n.status='approved'");
$stmt->execute([$news_id]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$news) {
    die("Article not found.");
}

// Fetch user reaction if logged in
$userReaction = null;
if (isset($_SESSION['user_id'])) {
    $ur = $conn->prepare("SELECT type FROM reactions WHERE user_id=? AND news_id=?");
    $ur->execute([$_SESSION['user_id'], $news_id]);
    $result = $ur->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $userReaction = $result['type'];
    }
}

// Count all reactions
$countStmt = $conn->prepare("SELECT type, COUNT(*) as total FROM reactions WHERE news_id=? GROUP BY type");
$countStmt->execute([$news_id]);
$reactionCounts = $countStmt->fetchAll(PDO::FETCH_ASSOC);

$counts = [
    "like" => 0,
    "love" => 0,
    "wow" => 0,
    "sad" => 0,
    "angry" => 0
];

foreach ($reactionCounts as $rc) {
    if (isset($counts[$rc['type']])) {
        $counts[$rc['type']] = $rc['total'];
    }
}

include "../includes/header.php";
?>

<style>
.article-img {
    width: 100%;
    height: 350px;
    object-fit: cover;
    border-radius: 10px;
}
.reaction-btn {
    transition: 0.2s;
}
.reaction-btn.active {
    transform: scale(1.1);
    font-weight: bold;
}
</style>

<div class="container mt-4">

    <h1 class="mb-3"><?php echo htmlspecialchars($news['title']); ?></h1>

    <p class="text-muted">
        By <strong><?php echo $news['first_name'] . " " . $news['last_name']; ?></strong> 
        • <?php echo date("F j, Y", strtotime($news['created_at'])); ?>
    </p>

    <?php if ($news['image_path']) : ?>
        <img src="<?php echo "../" . $news['image_path']; ?>" class="article-img mb-4">
    <?php endif; ?>

    <p class="lead">
        <?php echo nl2br(htmlspecialchars($news['summary'])); ?>
    </p>

    <hr>

    <div class="mt-4">
        <h4>Reactions</h4>

        <?php if (isset($_SESSION['user_id'])) : ?>
            <div class="d-flex gap-2 mt-2">

                <!-- Like -->
                <a href="like.php?news_id=<?php echo $news_id; ?>&type=like"
                   class="btn btn-sm reaction-btn <?php echo ($userReaction == 'like' ? 'btn-primary active' : 'btn-outline-primary'); ?>">
                   👍 Like (<?php echo $counts['like']; ?>)
                </a>

                
                <!-- <a href="like.php?news_id=<?php echo $news_id; ?>&type=love"
                   class="btn btn-sm reaction-btn <?php echo ($userReaction == 'love' ? 'btn-danger active' : 'btn-outline-danger'); ?>">
                   ❤️ Love (<?php echo $counts['love']; ?>)
                </a>

               
                <a href="like.php?news_id=<?php echo $news_id; ?>&type=wow"
                   class="btn btn-sm reaction-btn <?php echo ($userReaction == 'wow' ? 'btn-warning active' : 'btn-outline-warning'); ?>">
                   😮 Wow (<?php echo $counts['wow']; ?>)
                </a>

                
                <a href="like.php?news_id=<?php echo $news_id; ?>&type=sad"
                   class="btn btn-sm reaction-btn <?php echo ($userReaction == 'sad' ? 'btn-info active' : 'btn-outline-info'); ?>">
                   😢 Sad (<?php echo $counts['sad']; ?>)
                </a>

                
                <a href="like.php?news_id=<?php echo $news_id; ?>&type=angry"
                   class="btn btn-sm reaction-btn <?php echo ($userReaction == 'angry' ? 'btn-dark active' : 'btn-outline-dark'); ?>">
                   😡 Angry (<?php echo $counts['angry']; ?>)
                </a> -->

            </div>
        <?php else : ?>
            <p class="text-muted mt-2">Login to add reactions.</p>
        <?php endif; ?>
    </div>

    <hr>

    <h4>Full Story</h4>
    <p><?php echo nl2br(htmlspecialchars($news['body'])); ?></p>

    <hr>

    <a href="dashboard.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>

</div>

<?php include "../includes/footer.php"; ?>
