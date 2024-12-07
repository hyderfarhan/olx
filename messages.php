<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all messages for the current user
$stmt = $conn->prepare("
    SELECT m.*, u_sender.username as sender_username, u_receiver.username as receiver_username, a.title as ad_title, a.id as ad_id
    FROM messages m
    JOIN users u_sender ON m.sender_id = u_sender.id
    JOIN users u_receiver ON m.receiver_id = u_receiver.id
    JOIN ads a ON m.ad_id = a.id
    WHERE m.sender_id = ? OR m.receiver_id = ?
    ORDER BY m.created_at DESC
");
$stmt->execute([$user_id, $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle reply form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reply'])) {
    $reply = $_POST['reply'];
    $ad_id = $_POST['ad_id'];
    $receiver_id = $_POST['receiver_id'];

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, ad_id, message) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $receiver_id, $ad_id, $reply])) {
        header("Location: messages.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - OLX Inspired</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
            padding: 20px;
        }
        .message {
            background: #fff;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .message-content {
            margin-bottom: 15px;
        }
        .reply-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .reply-form button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .reply-form button:hover {
            background-color: #45a049;
        }
        .sender {
            font-weight: bold;
            color: #4CAF50;
        }
        .receiver {
            font-weight: bold;
            color: #2196F3;
        }
        .timestamp {
            color: #757575;
            font-size: 0.9em;
        }
        .ad-link {
            color: #FF5722;
            text-decoration: none;
        }
        .ad-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Your Messages</h1>
        <?php if (empty($messages)): ?>
            <p>You don't have any messages yet.</p>
        <?php else: ?>
            <?php foreach ($messages as $message): ?>
                <div class="message">
                    <div class="message-header">
                        <div>
                            <?php if ($message['sender_id'] == $user_id): ?>
                                <span class="sender">You</span> to <span class="receiver"><?php echo htmlspecialchars($message['receiver_username']); ?></span>
                            <?php else: ?>
                                <span class="sender"><?php echo htmlspecialchars($message['sender_username']); ?></span> to <span class="receiver">You</span>
                            <?php endif; ?>
                        </div>
                        <div>
                            Ad: <a href="ad_details.php?id=<?php echo $message['ad_id']; ?>" class="ad-link">
                                <?php echo htmlspecialchars($message['ad_title']); ?>
                            </a>
                        </div>
                        <div class="timestamp"><?php echo date('M j, Y H:i', strtotime($message['created_at'])); ?></div>
                    </div>
                    <div class="message-content">
                        <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                    </div>
                    <form class="reply-form" method="post" action="messages.php">
                        <input type="hidden" name="ad_id" value="<?php echo $message['ad_id']; ?>">
                        <input type="hidden" name="receiver_id" value="<?php echo ($message['sender_id'] == $user_id) ? $message['receiver_id'] : $message['sender_id']; ?>">
                        <textarea name="reply" placeholder="Write your reply..." required></textarea>
                        <button type="submit">Reply</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>

