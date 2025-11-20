<?php
require_once "../includes/db.php";
session_start();

// Only EIC
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'editor_in_chief'){
    header("Location: login.php");
    exit;
}

$eic_id = $_SESSION['user_id'];

// SEND MESSAGE
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $receiver_id = $_POST['receiver_id'];
    $body = trim($_POST['body']);

    if($receiver_id && $body){
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, body) VALUES (?, ?, ?)");
        $stmt->execute([$eic_id, $receiver_id, $body]);
    }
}

// FETCH ALL USERS except the EIC
$stmt = $conn->prepare("
    SELECT id, first_name, last_name, role 
    FROM users 
    WHERE id != ?
    ORDER BY role, first_name
");
$stmt->execute([$eic_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// SELECTED USER
$selected_user_id = $_GET['user_id'] ?? ($users[0]['id'] ?? null);

// FETCH MESSAGES
$messages = [];
if($selected_user_id){
    $stmt = $conn->prepare("
        SELECT m.*, u1.first_name AS sender_name
        FROM messages m
        JOIN users u1 ON m.sender_id = u1.id
        WHERE (sender_id=? AND receiver_id=?) 
           OR (sender_id=? AND receiver_id=?)
        ORDER BY created_at ASC
    ");
    $stmt->execute([$eic_id, $selected_user_id, $selected_user_id, $eic_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

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

.chat-box {
    height: 450px;
    overflow-y: auto;
    padding: 15px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.message {
    margin-bottom: 10px;
    padding: 10px 15px;
    border-radius: 10px;
    max-width: 75%;
}

.message.you {
    background-color: #00BFFF;
    color: white;
    margin-left: auto;
}

.message.they {
    background-color: #e0e0e0;
    color: black;
}
</style>

<!-- SIDEBAR -->
<div class="sidebar d-flex flex-column">
    <h4 class="mb-4 mt-2">Pen Press - EIC</h4>

    <ul class="nav flex-column">
        <li class="nav-item mb-2"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item mb-2"><a class="nav-link" href="assign_task.php">Assign Task</a></li>
        <li class="nav-item mb-2"><a class="nav-link active" href="messages.php">Messages</a></li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="all_news.php">All News</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="create_editor.php">Create Editor / Reporter</a>
        </li>
        <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php">Logout</a></li>
    </ul>
</div>

<main>
    <h2>Messages</h2>

    <div class="row">

        <!-- USER LIST -->
        <div class="col-md-3">
            <div class="list-group">

                <?php foreach($users as $u): ?>
                    <a href="messages.php?user_id=<?php echo $u['id']; ?>" 
                       class="list-group-item list-group-item-action <?php echo $selected_user_id==$u['id']?'active':''; ?>">
                       
                        <?php echo $u['first_name'] . " " . $u['last_name']; ?>
                        <br>
                        <small class="text-muted">
                            <?php echo ucfirst(str_replace("_", " ", $u['role'])); ?>
                        </small>
                    </a>
                <?php endforeach; ?>

            </div>
        </div>

        <!-- CHAT BOX -->
        <div class="col-md-9">
            <div class="chat-box mb-3">

                <?php if(empty($messages)): ?>
                    <p class="text-muted">No messages yet. Start the conversation.</p>
                <?php else: ?>

                    <?php foreach($messages as $msg): ?>
                        <div class="message <?php echo $msg['sender_id']==$eic_id ? 'you' : 'they'; ?>">
                            <?php echo htmlspecialchars($msg['body']); ?>
                            <br>
                            <small>
                                <?php echo $msg['sender_id']==$eic_id ? 'You' : $msg['sender_name']; ?>
                                | <?php echo $msg['created_at']; ?>
                            </small>
                        </div>
                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

            <!-- INPUT FIELD -->
            <?php if($selected_user_id): ?>
                <form method="post" class="d-flex">
                    <input type="hidden" name="receiver_id" value="<?php echo $selected_user_id; ?>">
                    <input type="text" name="body" class="form-control me-2" placeholder="Type a message..." required>
                    <button class="btn btn-primary">Send</button>
                </form>
            <?php endif; ?>

        </div>

    </div>

</main>

<?php include "../includes/footer.php"; ?>
