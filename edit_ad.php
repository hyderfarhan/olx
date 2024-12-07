<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$ad_id = $_GET['id'] ?? null;

if (!$ad_id) {
    header("Location: dashboard.php");
    exit();
}

// Fetch the ad
$stmt = $conn->prepare("SELECT * FROM ads WHERE id = ? AND user_id = ?");
$stmt->execute([$ad_id, $user_id]);
$ad = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ad) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];
    
    // Handle file upload
    $image_url = $ad['image_url']; // Keep the existing image by default
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = $target_file;
        }
    }

    $stmt = $conn->prepare("UPDATE ads SET title = ?, description = ?, price = ?, category = ?, image_url = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$title, $description, $price, $category, $image_url, $ad_id, $user_id]);

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ad - OLX Inspired</title>
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
        .edit-form {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 1em;
        }
        .edit-form input, .edit-form textarea, .edit-form select {
            display: block;
            width: 100%;
            margin-bottom: 1em;
            padding: 0.5em;
        }
        .edit-form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 0.5em 1em;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <h1>Edit Ad</h1>
    </header>
    <nav>
        <a href="index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </nav>
    <div class="container">
        <div class="edit-form">
            <h2>Edit Your Ad</h2>
            <form action="edit_ad.php?id=<?php echo $ad_id; ?>" method="post" enctype="multipart/form-data">
                <input type="text" name="title" placeholder="Title" value="<?php echo htmlspecialchars($ad['title']); ?>" required>
                <textarea name="description" placeholder="Description" required><?php echo htmlspecialchars($ad['description']); ?></textarea>
                <input type="number" name="price" placeholder="Price" step="0.01" value="<?php echo htmlspecialchars($ad['price']); ?>" required>
                <select name="category" required>
                    <option value="">Select Category</option>
                    <option value="Electronics" <?php echo $ad['category'] == 'Electronics' ? 'selected' : ''; ?>>Electronics</option>
                    <option value="Vehicles" <?php echo $ad['category'] == 'Vehicles' ? 'selected' : ''; ?>>Vehicles</option>
                    <option value="Furniture" <?php echo $ad['category'] == 'Furniture' ? 'selected' : ''; ?>>Furniture</option>
                    <option value="Clothing" <?php echo $ad['category'] == 'Clothing' ? 'selected' : ''; ?>>Clothing</option>
                </select>
                <input type="file" name="image" accept="image/*">
                <?php if ($ad['image_url']): ?>
                    <p>Current image: <img src="<?php echo htmlspecialchars($ad['image_url']); ?>" alt="Current ad image" style="max-width: 200px;"></p>
                <?php endif; ?>
                <button type="submit">Update Ad</button>
            </form>
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