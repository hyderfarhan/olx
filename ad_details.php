<?php
session_start();
require_once 'db_connect.php';

if (!isset($_GET['id'])) {
    header("Location: ./index.php");
    exit();
}

$ad_id = $_GET['id'];

$stmt = $conn->prepare("SELECT ads.*, users.username, users.email FROM ads JOIN users ON ads.user_id = users.id WHERE ads.id = ?");
$stmt->execute([$ad_id]);
$ad = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ad) {
    header("Location: ./index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $message = $_POST['message'];
    $sender_id = $_SESSION['user_id'];
    $receiver_id = $ad['user_id'];

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, ad_id, message) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$sender_id, $receiver_id, $ad_id, $message])) {
        $success = "Message sent successfully!";
    } else {
        $error = "Error sending message. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($ad['title']); ?> - OLX Inspired</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }
        nav {
            background-color: #333;
            padding: 10px;
            margin-bottom: 20px;
        }
        nav a {
            color: white;
            text-decoration: none;
            padding: 10px;
            margin-right: 10px;
        }
        nav a:hover {
            background-color: #555;
            border-radius: 4px;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #4CAF50;
        }
        img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .message-form {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .message-form textarea {
            width: 100%;
            height: 100px;
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .message-form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
        }
        .message-form button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            padding: 10px;
            background-color: #ffe6e6;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            padding: 10px;
            background-color: #e6ffe6;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .ad-details {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .price {
            font-size: 24px;
            color: #4CAF50;
            font-weight: bold;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <nav>
        <a href="./index.php">Home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="./dashboard.php">Dashboard</a>
            <a href="./messages.php">Messages</a>
            <a href="./logout.php">Logout</a>
        <?php else: ?>
            <a href="./login.php">Login</a>
            <a href="./signup.php">Sign Up</a>
        <?php endif; ?>
    </nav>
    <div class="container">
        <h1><?php echo htmlspecialchars($ad['title']); ?></h1>
        <div class="ad-details">
            <?php if ($ad['image_url']): ?>
                <img src="<?php echo htmlspecialchars($ad['image_url']); ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>">
            <?php endif; ?>
            <div class="price">$<?php echo htmlspecialchars($ad['price']); ?></div>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($ad['category']); ?></p>
            <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($ad['description'])); ?></p>
            <p><strong>Seller:</strong> <?php echo htmlspecialchars($ad['username']); ?></p>
        </div>
        
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $ad['user_id']): ?>
            <div class="message-form">
                <h2>Contact Seller</h2>
                <?php if ($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <?php if ($success): ?>
                    <p class="success"><?php echo $success; ?></p>
                <?php endif; ?>
                <form action="./ad_details.php?id=<?php echo $ad_id; ?>" method="post">
                    <textarea name="message" placeholder="Your message" required></textarea>
                    <button type="submit">Send Message</button>
                </form>
            </div>
        <?php elseif (!isset($_SESSION['user_id'])): ?>
            <p>Please <a href="./login.php">log in</a> to contact the seller.</p>
        <?php endif; ?>
    </div>
</body>
</html>

