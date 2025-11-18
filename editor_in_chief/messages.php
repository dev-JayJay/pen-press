<?php
require_once "../includes/db.php";
session_start();


if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'editor_in_chief'){
    header("Location: login.php");
    exit;
}


if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $receiver_id = $_POST['receiver_id'];
    $body = trim($_POST['body']);

    if($receiver_id && $body){
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, body) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $receiver_id, $body]);
    }
}

$stmt = $conn->prepare("SELECT id, first_name, last_name, editor_type FROM users WHERE role='editor' ORDER BY editor_type, first_name");
$stmt->execute();
$editors = $stmt->fetchAll(PDO::FETCH_ASSOC);


$selected_editor_id = isset($_GET['editor_id']) ? $_GET['editor_id'] : ($editors[0]['id'] ?? null);


$messages = [];
if($selected_editor_id){
    $stmt = $conn->prepare("SELECT m.*, u1.first_name AS sender_name, u2.first_name AS receiver_name
                            FROM messages m
                            JOIN users u1 ON m.sender_id = u1.id
                            JOIN users u2 ON m.receiver_id = u2.id
                            WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)
                            ORDER BY created_at ASC");
    $stmt->execute([$_SESSION['user_id'], $selected_editor_id, $selected_editor_id, $_SESSION['user_id']]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include "../includes/header.php";
?>

<style>
    body {
        background-color: #f5f6fa;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

    /* Cards */
    .card {
        border-radius: 10px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    .card-title {
        font-weight: 600;
        color: #1E1E2F;
    }
    .card-text {
        color: #555;
    }
    .btn-primary {
        background-color: #00BFFF;
        border-color: #00BFFF;
    }
    .btn-primary:hover {
        background-color: #009acd;
        border-color: #009acd;
    }
    .btn-outline-primary {
        border-color: #00BFFF;
        color: #00BFFF;
    }
    .btn-outline-primary:hover {
        background-color: #00BFFF;
        color: #fff;
    }

    @media(max-width: 768px) {
        .sidebar {
            position: relative;
            width: 100%;
            height: auto;
        }
        main {
            margin-left: 0;
            padding: 20px;
        }
    }
</style>

<div class="sidebar d-flex flex-column">
    <h4 class="mb-4 mt-2">Pen Press - EIC</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link active" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="assign_task.php">Assign Task</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="create_editor.php">Create Editor</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="messages.php">Messages</a>
        </li>
        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </li>
    </ul>
</div>


<!-- Sidebar -->
<div class="sidebar d-flex flex-column">
    <h4 class="mb-4 mt-2">Pen Press News - EIC</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-2">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="assign_task.php">Assign Task</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link" href="create_editor.php">Create Editor</a>
        </li>
        <li class="nav-item mb-2">
            <a class="nav-link active" href="messages.php">Messages</a>
        </li>
        <li class="nav-item mt-4">
            <a class="nav-link text-danger" href="logout.php">Logout</a>
        </li>
    </ul>
</div>

<main>
    <h2>Messages</h2>
    <div class="row">
        <!-- Editor List -->
        <div class="col-md-3 mb-3">
            <div class="list-group">
                <?php foreach($editors as $editor): ?>
                    <a href="messages.php?editor_id=<?php echo $editor['id']; ?>" 
                       class="list-group-item list-group-item-action <?php echo $editor['id']==$selected_editor_id?'active':''; ?>">
                        <?php echo ucfirst($editor['editor_type']) . ' - ' . $editor['first_name'] . ' ' . $editor['last_name']; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Chat Box -->
        <div class="col-md-9">
            <div class="card shadow-sm mb-3" style="height:500px; overflow-y:auto; padding:15px;">
                <?php if(empty($messages)): ?>
                    <p class="text-muted">No messages yet. Start the conversation!</p>
                <?php else: ?>
                    <?php foreach($messages as $msg): ?>
                        <div class="mb-2">
                            <strong><?php echo $msg['sender_id']==$_SESSION['user_id'] ? 'You' : $msg['sender_name']; ?>:</strong>
                            <span><?php echo htmlspecialchars($msg['body']); ?></span>
                            <div class="text-muted" style="font-size:0.75rem;"><?php echo $msg['created_at']; ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Message Form -->
            <?php if($selected_editor_id): ?>
            <form method="post" class="d-flex">
                <input type="hidden" name="receiver_id" value="<?php echo $selected_editor_id; ?>">
                <input type="text" name="body" class="form-control me-2" placeholder="Type a message..." required>
                <button class="btn btn-primary">Send</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include "../includes/footer.php"; ?>
