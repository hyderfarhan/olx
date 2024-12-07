<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user information
$stmt = $conn->prepare("SELECT username, profile_image FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user's ads
$stmt = $conn->prepare("SELECT * FROM ads WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    
    // Handle file upload
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/ads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if($check !== false) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO ads (user_id, title, description, price, category, image_url) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $title, $description, $price, $category, $image_url]);

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - OLX Inspired</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }
        header {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 1em;
        }
        nav {
            background-color: #333;
            color: white;
            padding: 0.5em;
        }
        nav a {
            color: white;
            text-decoration: none;
            padding: 0.5em;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 1em;
        }
        .profile {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 1em;
            margin-bottom: 1em;
            display: flex;
            align-items: center;
        }
        .profile img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1em;
        }
        .ad-form, .ad-list {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 1em;
            margin-bottom: 1em;
        }
        .ad-form input, .ad-form textarea, .ad-form select {
            display: block;
            width: 100%;
            margin-bottom: 1em;
            padding: 0.5em;
        }
        .ad-form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 0.5em 1em;
            cursor: pointer;
        }
        .ad-item {
            border-bottom: 1px solid #ddd;
            padding: 1em 0;
        }
        .ad-item:last-child {
            border-bottom: none;
        }
        .ad-item img {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            margin-right: 1em;
        }
    </style>
</head>
<body>
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>
    </header>
    <nav>
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
        <a href="messages.php">Messages</a>
    </nav>
    <div class="container">
        <div class="profile">
            <?php if ($user['profile_image']): ?>
                <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Picture">
            <?php else: ?>
                <img src="https://via.placeholder.com/100" alt="Default Profile Picture">
            <?php endif; ?>
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
        </div>
        <div class="ad-form">
            <h2>Post a New Ad</h2>
            <form action="dashboard.php" method="post" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Title" required>
                <textarea name="description" placeholder="Description" required></textarea>
                <input type="number" name="price" placeholder="Price" step="0.01" required>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option value="Electronics">Electronics</option>
                    <option value="Vehicles">Vehicles</option>
                    <option value="Furniture">Furniture</option>
                    <option value="Clothing">Clothing</option>
                </select>
                <input type="file" name="image" accept="image/*">
                <button type="submit">Post Ad</button>
            </form>
        </div>
        <div class="ad-list">
            <h2>Your Ads</h2>
            <?php foreach ($ads as $ad): ?>
                <div class="ad-item">
                    <?php if ($ad['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($ad['image_url']); ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($ad['title']); ?></h3>
                    <p>Price: $<?php echo htmlspecialchars($ad['price']); ?></p>
                    <p>Category: <?php echo htmlspecialchars($ad['category']); ?></p>
                    <p><?php echo htmlspecialchars(substr($ad['description'], 0, 100)); ?>...</p>
                    <a href="edit_ad.php?id=<?php echo $ad['id']; ?>">Edit</a>
                    <a href="delete_ad.php?id=<?php echo $ad['id']; ?>" onclick="return confirm('Are you sure you want to delete this ad?');">Delete</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            let title = document.querySelector('input[name="title"]').value;
            let description = document.querySelector('textarea[name="description"]').value;
            let price = document.querySelector('input[name="price"]').value;
            let category = document.querySelector('select[name="category"]').value;

            if (title.trim() === '' || description.trim() === '' || price.trim() === '' || category === '') {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    </script>
</body>
</html>

