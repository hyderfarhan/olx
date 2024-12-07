<?php
session_start();
require_once 'db_connect.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$query = "SELECT * FROM ads WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR description LIKE ? OR category LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}

if (!empty($category)) {
    $query .= " AND category = ?";
    $params[] = $category;
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$ads = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = ['Electronics', 'Vehicles', 'Furniture', 'Clothing'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OLX Inspired - Classified Ads</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 1em;
        }
        .search-form {
            display: flex;
            gap: 1em;
            margin-bottom: 1em;
        }
        .search-form input[type="text"],
        .search-form select {
            flex-grow: 1;
            padding: 0.5em;
        }
        .search-form button {
            padding: 0.5em 1em;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .ad-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1em;
        }
        .ad-item {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 1em;
        }
        .ad-item img {
            max-width: 100%;
            height: auto;
            object-fit: cover;
            margin-bottom: 0.5em;
        }
    </style>
</head>
<body>
    <header>
        <h1>OLX Inspired - Classified Ads</h1>
    </header>
    <nav>
        <a href="index.php">Home</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="signup.php">Sign Up</a>
        <?php endif; ?>
    </nav>
    <div class="container">
        <form class="search-form" action="index.php" method="GET">
            <input type="text" name="search" placeholder="Search ads..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat; ?>" <?php echo $category == $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Search</button>
        </form>
        <div class="ad-grid">
            <?php foreach ($ads as $ad): ?>
                <div class="ad-item">
                    <?php if ($ad['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($ad['image_url']); ?>" alt="<?php echo htmlspecialchars($ad['title']); ?>">
                    <?php endif; ?>
                    <h3><?php echo htmlspecialchars($ad['title']); ?></h3>
                    <p>Price: $<?php echo htmlspecialchars($ad['price']); ?></p>
                    <p>Category: <?php echo htmlspecialchars($ad['category']); ?></p>
                    <a href="ad_details.php?id=<?php echo $ad['id']; ?>">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>

