<?php
session_start();
require_once 'db_connect.php';

// Check if upload directories exist
$required_directories = ['uploads/profiles', 'uploads/ads'];
foreach ($required_directories as $dir) {
    if (!file_exists($dir)) {
        die("Error: Required directory '$dir' does not exist. Please run setup.php first.");
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $error = "An account with this email already exists.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Handle file upload
        $profile_image = null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $target_dir = "uploads/profiles/";
            $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Check if image file is an actual image or fake image
            $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
            if($check !== false) {
                if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                    $profile_image = $target_file;
                }
            }
        }

        $stmt = $conn->prepare("INSERT INTO users (username, email, password, profile_image) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashed_password, $profile_image])) {
            $success = "Account created successfully. Please log in.";
        } else {
            $error = "Error creating account. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - OLX Inspired</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #4CAF50;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input {
            margin: 10px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sign Up</h1>
        <?php 
        if ($error) echo "<p class='error'>$error</p>";
        if ($success) echo "<p class='success'>$success</p>";
        ?>
        <form action="signup.php" method="post" enctype="multipart/form-data">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="file" name="profile_image" accept="image/*">
            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            let username = document.querySelector('input[name="username"]').value;
            let email = document.querySelector('input[name="email"]').value;
            let password = document.querySelector('input[name="password"]').value;

            if (username.trim() === '' || email.trim() === '' || password.trim() === '') {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    </script>
</body>
</html>

